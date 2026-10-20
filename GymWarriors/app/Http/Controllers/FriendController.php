<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FriendController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $friendships = Friendship::with(['requester:id,first_name,last_name,email', 'addressee:id,first_name,last_name,email'])
            ->where(function ($query) use ($user) {
                $query->where('requester_id', $user->id)->orWhere('addressee_id', $user->id);
            })
            ->get();

        $incoming = $friendships
            ->where('status', 'pending')
            ->where('addressee_id', $user->id)
            ->map(fn (Friendship $friendship) => $this->presentUser($friendship->requester, $friendship->id))
            ->values();

        $outgoing = $friendships
            ->where('status', 'pending')
            ->where('requester_id', $user->id)
            ->map(fn (Friendship $friendship) => $this->presentUser($friendship->addressee, $friendship->id))
            ->values();

        return response()->json([
            'friends' => $friendships
                ->where('status', 'accepted')
                ->map(function (Friendship $friendship) use ($user) {
                    $friend = $friendship->requester_id === $user->id
                        ? $friendship->addressee
                        : $friendship->requester;

                    return $this->presentFriend($friend, $friendship->id);
                })
                ->values(),
            'incoming_requests' => $incoming,
            'outgoing_requests' => $outgoing,
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate(['query' => ['required', 'string', 'min:2', 'max:100']]);
        $user = $request->user();

        return response()->json([
            'users' => User::query()
                ->where('id', '!=', $user->id)
                ->where(function ($query) use ($validated) {
                    $term = $validated['query'];
                    $query->where('email', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                })
                ->select('id', 'first_name', 'last_name', 'email')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $user = $request->user();

        if ((int) $validated['user_id'] === $user->id) {
            throw ValidationException::withMessages(['user_id' => 'Нельзя отправить заявку самому себе.']);
        }

        $existing = Friendship::query()
            ->where(function ($query) use ($user, $validated) {
                $query->where('requester_id', $user->id)->where('addressee_id', $validated['user_id']);
            })
            ->orWhere(function ($query) use ($user, $validated) {
                $query->where('requester_id', $validated['user_id'])->where('addressee_id', $user->id);
            })
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                throw ValidationException::withMessages(['user_id' => 'Пользователь уже в списке друзей.']);
            }

            throw ValidationException::withMessages(['user_id' => 'Заявка уже существует.']);
        }

        Friendship::create([
            'requester_id' => $user->id,
            'addressee_id' => $validated['user_id'],
        ]);

        return response()->json(['message' => 'Заявка отправлена.'], 201);
    }

    public function update(Request $request, Friendship $friendship)
    {
        $validated = $request->validate(['status' => ['required', 'in:accepted,rejected']]);

        abort_unless($friendship->addressee_id === $request->user()->id, 403);
        abort_unless($friendship->status === 'pending', 422, 'Заявка уже обработана.');

        $friendship->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Заявка обновлена.']);
    }

    public function destroy(Request $request, Friendship $friendship)
    {
        $userId = $request->user()->id;
        abort_unless($friendship->requester_id === $userId || $friendship->addressee_id === $userId, 403);
        $friendship->delete();

        return response()->json(['message' => 'Связь удалена.']);
    }

    private function presentUser(?User $user, int $requestId): array
    {
        return [
            'request_id' => $requestId,
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];
    }

    private function presentFriend(User $friend, int $friendshipId): array
    {
        return [
            'friendship_id' => $friendshipId,
            'id' => $friend->id,
            'first_name' => $friend->first_name,
            'last_name' => $friend->last_name,
            'email' => $friend->email,
            'workouts_count' => $friend->workouts()->count(),
            'checkins_count' => $friend->planPurchases()->whereHas('checkins')->withCount('checkins')->get()->sum('checkins_count'),
        ];
    }
}
