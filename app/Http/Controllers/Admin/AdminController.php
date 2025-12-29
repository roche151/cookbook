<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecipeRevision;
use App\Models\User;
use App\Models\Feedback;

class AdminController extends Controller
{
    public function index()
    {
        $pendingCount = RecipeRevision::where('status', 'pending')->count();
        $usersCount = User::count();
        $feedbackCount = Feedback::count();

        return view('admin.index', compact('pendingCount', 'usersCount', 'feedbackCount'));
    }

    public function listUsers()
    {
        $users = User::orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function showUser(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function toggleAdmin(User $user)
    {
        $user->is_admin = !$user->is_admin;
        $user->save();

        return redirect()->route('admin.users.show', $user)->with('success', 'User admin status updated.');
    }

    public function toggleVerified(User $user)
    {
        if ($user->email_verified_at) {
            $user->email_verified_at = null;
        } else {
            $user->email_verified_at = now();
        }
        $user->save();

        return redirect()->route('admin.users.show', $user)->with('success', 'User email verification status updated.');
    }

    public function viewFeedback()
    {
        $feedback = Feedback::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.feedback.index', compact('feedback'));
    }
}