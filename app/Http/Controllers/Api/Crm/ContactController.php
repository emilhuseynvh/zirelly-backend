<?php

namespace App\Http\Controllers\Api\Crm;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\ContactNoteResource;
use App\Http\Resources\Crm\ContactResource;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\ContactNote;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $contacts = $this->filteredQuery($request)
            ->paginate($request->integer('per_page', 20));

        return ContactResource::collection($contacts);
    }

    public function show(Contact $contact): ContactResource
    {
        $contact = $this->withStats(Contact::query())->findOrFail($contact->id);

        $contact->load([
            'notes.author',
            'orders' => fn ($q) => $q->with('contact', 'user')->withCount('items')->latest('id')->limit(50),
        ]);

        return new ContactResource($contact);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        if ($duplicate = $this->findDuplicate($data)) {
            return response()->json([
                'message' => "Bu telefon və ya e-poçt ilə müştəri artıq mövcuddur (#{$duplicate->id} — {$duplicate->name}).",
            ], 422);
        }

        $contact = Contact::query()->create($data);

        AuditLog::record($request->user(), 'contact_created', $contact, $data);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function update(Request $request, Contact $contact): ContactResource
    {
        $data = $this->validated($request);

        if ($duplicate = $this->findDuplicate($data, $contact->id)) {
            abort(422, "Bu telefon və ya e-poçt ilə müştəri artıq mövcuddur (#{$duplicate->id} — {$duplicate->name}).");
        }

        $changes = array_diff_assoc(
            array_map(fn ($v) => (string) $v, $data),
            array_map(fn ($v) => (string) $v, $contact->only(array_keys($data))),
        );

        $contact->update($data);

        AuditLog::record($request->user(), 'contact_updated', $contact, $changes ?: null);

        return new ContactResource($contact);
    }

    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        $contact->delete();

        AuditLog::record($request->user(), 'contact_deleted', $contact);

        return response()->json(['message' => 'Müştəri arxivləşdirildi.']);
    }

    public function storeNote(Request $request, Contact $contact): JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $note = $contact->notes()->create([
            'crm_user_id' => $request->user()->id,
            'body' => $request->input('body'),
        ]);

        AuditLog::record($request->user(), 'contact_note_added', $contact);

        return (new ContactNoteResource($note->load('author')))->response()->setStatusCode(201);
    }

    public function updateNote(Request $request, Contact $contact, ContactNote $note): ContactNoteResource
    {
        $this->authorizeNote($request, $contact, $note);

        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $note->update(['body' => $request->input('body')]);

        AuditLog::record($request->user(), 'contact_note_updated', $contact, ['note_id' => $note->id]);

        return new ContactNoteResource($note->load('author'));
    }

    public function destroyNote(Request $request, Contact $contact, ContactNote $note): JsonResponse
    {
        $this->authorizeNote($request, $contact, $note);

        $note->delete();

        AuditLog::record($request->user(), 'contact_note_deleted', $contact, ['note_id' => $note->id]);

        return response()->json(['message' => 'Qeyd silindi.']);
    }

    protected function authorizeNote(Request $request, Contact $contact, ContactNote $note): void
    {
        abort_unless($note->contact_id === $contact->id, 404);

        $user = $request->user();

        abort_unless(
            $user->isSuperadmin() || $note->crm_user_id === $user->id,
            403,
            'Yalnız öz qeydinizi dəyişə bilərsiniz.',
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $contacts = $this->filteredQuery($request)->get();

        $filename = 'musteriler-'.now()->format('Y-m-d-Hi').'.csv';

        return response()->streamDownload(function () use ($contacts) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                '№', 'Ad', 'Soyad', 'Telefon', 'E-poçt', 'Doğum tarixi', 'Kanal', 'Tip',
                'Sifariş sayı', 'Ümumi alış', 'İlk sifariş', 'Son sifariş', 'Yaradılma tarixi',
            ]);

            foreach ($contacts as $contact) {
                fputcsv($out, [
                    $contact->id,
                    $contact->name,
                    $contact->surname,
                    $contact->phone,
                    $contact->email,
                    $contact->birth_date?->format('d.m.Y'),
                    $contact->channel,
                    $contact->created_via === 'site' ? 'Saytdan' : 'CRM-dən',
                    (int) $contact->orders_count,
                    number_format((float) $contact->orders_total, 2, '.', ''),
                    $contact->first_order_at ? date('d.m.Y', strtotime($contact->first_order_at)) : '',
                    $contact->last_order_at ? date('d.m.Y', strtotime($contact->last_order_at)) : '',
                    $contact->created_at?->format('d.m.Y'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function checkPhone(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:30']]);

        $phone = Phone::normalize($request->input('phone'));

        $contact = Contact::query()
            ->where('phone', $phone)
            ->when($request->filled('except'), fn ($q) => $q->where('id', '!=', $request->integer('except')))
            ->first();

        return response()->json([
            'data' => $contact ? [
                'id' => $contact->id,
                'name' => trim($contact->name.' '.($contact->surname ?? '')),
                'phone' => $contact->phone,
                'email' => $contact->email,
            ] : null,
        ]);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $request->validate([
            'search' => ['sometimes', 'string', 'max:100'],
            'channel' => ['sometimes', Rule::in(Contact::CHANNELS)],
            'created_via' => ['sometimes', Rule::in(['site', 'crm'])],
            'has_orders' => ['sometimes', Rule::in(['yes', 'no'])],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'sort' => ['sometimes', Rule::in(['id', 'name', 'orders_count', 'orders_total', 'last_order_at', 'created_at'])],
            'dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);

        return $this->withStats(Contact::query())
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->input('channel')))
            ->when($request->filled('created_via'), fn ($q) => $q->where('created_via', $request->input('created_via')))
            ->when($request->input('has_orders') === 'yes', fn ($q) => $q->has('orders'))
            ->when($request->input('has_orders') === 'no', fn ($q) => $q->doesntHave('orders'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy(
                $request->input('sort', 'id'),
                $request->input('dir', 'desc'),
            );
    }

    protected function withStats(Builder $query): Builder
    {
        $paidStatuses = array_map(fn ($s) => $s->value, OrderStatus::paidLike());

        return $query
            ->withCount('orders')
            ->withSum(['orders as orders_total' => fn ($q) => $q->whereIn('status', $paidStatuses)], 'total')
            ->withMin(['orders as first_order_at'], 'created_at')
            ->withMax(['orders as last_order_at'], 'created_at');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'channel' => ['required', Rule::in(Contact::CHANNELS)],
        ]);

        if (filled($data['phone'] ?? null)) {
            $data['phone'] = Phone::normalize($data['phone']);
        }

        return $data;
    }

    protected function findDuplicate(array $data, ?int $exceptId = null): ?Contact
    {
        if (blank($data['phone'] ?? null) && blank($data['email'] ?? null)) {
            return null;
        }

        return Contact::query()
            ->when($exceptId !== null, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where(function ($q) use ($data) {
                if (filled($data['phone'] ?? null)) {
                    $q->orWhere('phone', $data['phone']);
                }
                if (filled($data['email'] ?? null)) {
                    $q->orWhere('email', $data['email']);
                }
            })
            ->first();
    }
}
