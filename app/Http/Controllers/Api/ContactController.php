<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ContactController extends Controller
{
    /**
     * Display a paginated list of the user's contacts.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 12), 5), 50);
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'cpf', 'created_at'];
        $sort = in_array($request->query('sort', 'name'), $allowedSorts, true)
            ? $request->query('sort', 'name')
            : 'name';

        $contacts = $request->user()
            ->contacts()
            ->when($request->query('search'), function ($query, $term) {
                $term = trim($term);

                if ($term === '') {
                    return;
                }

                $cpfTerm = preg_replace('/\\D/', '', $term);

                $query->where(function ($query) use ($term, $cpfTerm) {
                    $query->where('name', 'like', "%{$term}%");

                    if ($cpfTerm !== '') {
                        $query->orWhere('cpf', 'like', "%{$cpfTerm}%");
                    }
                });
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ContactResource::collection($contacts)->response();
    }

    /**
     * Store a newly created contact.
     */
    public function store(StoreContactRequest $request, GeocodingService $geocoder): JsonResponse
    {
        try {
            $contact = $this->persistContact($request->user(), $request->validated(), $geocoder);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * Create a mock contact for the authenticated user.
     */
    public function mock(Request $request): JsonResponse
    {
        abort_unless(config('features.mock_contacts'), 404);

        $contact = Contact::factory()
            ->for($request->user())
            ->brazilianCoordinates()
            ->create();

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * Show a single contact.
     */
    public function show(Request $request, Contact $contact): JsonResponse
    {
        $this->ensureOwnership($request->user()->id, $contact);

        return (new ContactResource($contact))->response();
    }

    /**
     * Update the specified contact.
     */
    public function update(UpdateContactRequest $request, Contact $contact, GeocodingService $geocoder): JsonResponse
    {
        $this->ensureOwnership($request->user()->id, $contact);

        try {
            $attributes = array_merge($request->validated(), $geocoder->geocode($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $contact->update($attributes);

        return (new ContactResource($contact))->response();
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        $this->ensureOwnership($request->user()->id, $contact);

        $contact->delete();

        return response()->json([], 204);
    }

    /**
     * Persist a contact for the given user.
     *
     * @return \App\Models\Contact
     */
    protected function persistContact($user, array $validated, GeocodingService $geocoder): Contact
    {
        $attributes = array_merge($validated, $geocoder->geocode($validated));

        return $user->contacts()->create($attributes);
    }

    /**
     * Ensure the authenticated user owns the contact.
     */
    protected function ensureOwnership(int $userId, Contact $contact): void
    {
        abort_unless($contact->user_id === $userId, 403);
    }
}
