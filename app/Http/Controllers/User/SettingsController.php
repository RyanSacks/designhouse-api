<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\IUser;
use App\Rules\CheckSamePassword;
use App\Rules\MatchOldPassword;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $users;

    public function __construct(IUser $users)
    {
        $this->users = $users;
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $this->validate($request, [
            'tagline' => ['required'],
            'name' => ['required'],
            'about' => ['required', 'string', 'min:20'],
            'formatted_address' => ['required'],
            // using point column attribute with laravel-mysql-spatial
            'location.latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'location.longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
        ]);

        $location = new Point($request->location['latitude'], $request->location['longitude']);

        $user = $this->users->update(auth()->id(), [
            'tagline' => $request->tagline,
            'name' => $request->name,
            'about' => $request->about,
            'available_to_hire' => $request->available_to_hire,
            'formatted_address' => $request->formatted_address,
            'location' => $location
        ]);

        return new UserResource($user);

    }

    public function updatePassword(Request $request)
    {
        // current password
        // new password
        // password confirmation
        $this->validate($request, [
           'current_password' => ['required', new MatchOldPassword],
           'password' => ['required', 'confirmed', 'min:6', new CheckSamePassword],
        ]);

        $request->user()->update([
           'password' => bcrypt($request->password)
        ]);

        return response()->json(['message' => 'Password Updated'], 200);
    }
}
