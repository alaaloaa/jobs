<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // return $request;
        // $path = $request->file('avatar')->store('public/images/users');
        // $user->avatar = url($path);

        // $user->save();

        // return $user->avatar;

        $request->validate([
            'name' => 'required|min:4|max:30',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'job' => 'required',
            'gender' => 'required', Rule::in(['male', 'female']),
            'country' => 'required',
            'info' => 'required|min:200|max:1000',
            // 'avatar' => 'nullable|image|max:100000',
            'skills' => 'required',
        ]);
        $user->update($request->except('avatar'));
        if ($request->hasFile('avatar') && !empty($request->file('avatar'))) {
            if (!empty($user->avatar)) {
                $path = str_replace(url('/storage'), 'storage', $user->avatar); // get old logo path
                // unlink(storage_path($path));

                // unlink($path); // delete old pic
            }
            Storage::disk('s3')->put('avatars/1', $request->file('avatar'));

            $path = $request->file('avatar')->store('public/images/users');
            $user->avatar = $path;
            $user->save();
        }
        return response()->json(['user' => $user, 'msg' => 'You updated your profile successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * Return User's Jobs
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function userJobs()
    {
        return response()->json(Job::orderDesc()->where('user_id', Auth::id())->get(['id', 'name']));
    }
}