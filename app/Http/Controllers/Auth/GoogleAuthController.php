<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
  //

  public function redirect()
  {
    return Socialite::driver('google')->redirect();
  }

  public function googleCallback()
  {
    try {
      $user = Socialite::driver('google')->user();

      // check if user already exists in our database by checking the google_id
      $existingUser = User::where('google_id', $user->id)->first();
      if ($existingUser) {
        Auth::login($existingUser);
        return to_route('dashboard');
      } else {
        // check first if email has the same email as the one in our database
        $newUser = User::where('email', $user->email)->first();
        if ($newUser) {
          $newUser->google_id = $user->id;
          $newUser->save();
        } else {
          // if not we need to create a new user and save it on the database
          $newUser = new User();
          $newUser->name = $user->name;
          $newUser->email = $user->email;
          $newUser->google_id = $user->id;
          $newUser->email_verified_at = now();
          $newUser->save();
        }

        // then save to global auth and redirect to dashboard
        Auth::login($newUser);
        return to_route('dashboard');
      }
    } catch (Exception $e) {
      dd($e->getMessage());
      return redirect('login');
    }
  }
}
