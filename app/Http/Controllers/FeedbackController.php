<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:bug,feature,general',
            'message' => 'required|string',
        ]);

        $feedback = new Feedback();
        $feedback->user_id = $request->user()->id;
        $feedback->type = $validated['type'];
        $feedback->description = $validated['message'];
        $referer = $request->headers->get('referer') ?? $request->input('page', url()->previous());
        $path = parse_url($referer, PHP_URL_PATH);
        $feedback->page = $path ? (str_starts_with($path, '/') ? $path : '/' . $path) : '/';
        $feedback->save();

        return response(['status' => 'success', 'message' => 'Thank you for your feedback!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
