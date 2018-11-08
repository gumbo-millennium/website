<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountDeletedMail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('user.index', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return Response
     */
    public function show(Request $request)
    {
        return view('user.info', [
            'user' => $request->user()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Request  $request
     * @param  \App\User  $user
     * @return \Response
     */
    public function update(Request $request)
    {
        // Get user
        $user = $request->user();

        // Validate request
        $valid = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email', $user->id)],

            // Names
            'first_name' => 'required|string|min:2',
            'insertion' => 'optional|string|min:2',
            'last_name' => 'required|string|min:2',
        ]);

        // Update user
        $user->fill([
            'email' => $valid->email,
            'first_name' => $valid->first_name,
            'insertion' => $valid->insertion,
            'last_name' => $valid->last_name,
        ]);

        // Only update certain fields
        $user->save(['email', 'first_name', 'insertion', 'last_name', 'updated_at']);

        // Redirect back
        return redirect()->route('user.info')->with([
            'status' => 'Je profiel is bijgewerkt'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Response
     */
    public function remove(User $user)
    {
        return view('user.remove', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Request  $request
     * @return \Response
     */
    public function destroy(Request $request)
    {
        // Remove user
        $user = $request->user();
        $user->remove();

        // Send e-mail
        Mail::to($user)
            ->queue(new AccountDeletedMail($user));

        return redirect('home');
    }
}
