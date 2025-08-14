<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroup;
use App\Models\GroupInvite;
use App\Models\UserGroupAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class GroupInviteController extends Controller
{
    public function generate(Request $request, int $groupId): JsonResponse
    {
        $user = $request->user();
        if (!$user->isManager() || !$user->isManagerOfGroup($groupId)) {
            abort(403);
        }

        $request->validate([
            'expires_in_days' => 'nullable|integer|min:1|max:365',
            'max_uses' => 'nullable|integer|min:1|max:10000',
        ]);

        // If a valid active invite already exists for this group, return it instead of creating a new one
        $existing = \App\Models\GroupInvite::where('device_group_id', $groupId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('uses', '<', 'max_uses');
            })
            ->latest('id')
            ->first();

        if ($existing) {
            $url = route('group-invites.show', ['token' => $existing->token]);
            return response()->json([
                'success' => true,
                'invite_url' => $url,
                'invite' => $existing,
                'already_exists' => true,
            ]);
        }

        $expiresAt = null;
        if ($request->filled('expires_in_days')) {
            $expiresAt = now()->addDays((int) $request->input('expires_in_days'));
        }

        $invite = GroupInvite::create([
            'device_group_id' => $groupId,
            'manager_id' => $user->id,
            'token' => Str::random(32),
            'expires_at' => $expiresAt,
            'max_uses' => $request->input('max_uses'),
            'uses' => 0,
            'is_active' => true,
        ]);

        $url = route('group-invites.show', ['token' => $invite->token]);

        return response()->json([
            'success' => true,
            'invite_url' => $url,
            'invite' => $invite,
            'already_exists' => false,
        ]);
    }

    public function show(Request $request, string $token): View
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();

        $user = $request->user();
        if (!$user) {
            return view('invites.login-required', [
                'invite' => $invite,
            ]);
        }

        $group = $invite->deviceGroup;

        return view('invites.confirm', [
            'invite' => $invite,
            'group' => $group,
            'manager' => $invite->manager,
            'isValid' => $invite->isValid(),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        if (!$invite->isValid()) {
            return view('invites.invalid', [
                'invite' => $invite,
            ]);
        }

        // Create or reactivate membership as member
        $assignment = UserGroupAssignment::firstOrNew([
            'user_id' => $user->id,
            'device_group_id' => $invite->device_group_id,
        ]);
        $assignment->role = $assignment->role ?: 'member';
        $assignment->is_active = true;
        $assignment->save();

        // Increment uses and deactivate if max reached
        $invite->increment('uses');
        if (!empty($invite->max_uses) && $invite->uses >= $invite->max_uses) {
            $invite->is_active = false;
            $invite->save();
        }

        return redirect()->route('dashboard');
    }
}


