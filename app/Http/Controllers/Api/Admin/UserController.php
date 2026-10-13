<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $users = User::orderBy('role')->orderBy('username')->get();

        return response()->json([
            'message' => 'Daftar user.',
            'data' => UserResource::collection($users),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'role' => $request->role,
        ]);

        $this->logActivity(
            'create_user',
            'users',
            $user->id,
            "Membuat user: {$user->username} ({$user->role})"
        );

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Detail user.',
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $this->logActivity(
            'update_user',
            'users',
            $user->id,
            "Memperbarui user: {$user->username}"
        );

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri.',
                'data' => null,
            ], 403);
        }

        $user->delete();

        $this->logActivity(
            'delete_user',
            'users',
            $user->id,
            "Menghapus user: {$user->username}"
        );

        return response()->json([
            'message' => 'User berhasil dihapus.',
            'data' => null,
        ]);
    }
}
