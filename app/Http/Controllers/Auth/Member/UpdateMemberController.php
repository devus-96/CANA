<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\PhoneNumber;

use App\Models\Member;
use App\Models\StateOfLive;

class UpdateMemberController
{
    public function update(Request $request, Member $member)
    {
        Validator::make($request->all(), [
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => 'sometimes|string|lowercase|email|max:255|unique:members,email,' . $member->id,
            'phone'         => ['sometimes', 'string', 'unique:'.Member::class, new PhoneNumber],
            "stateOfLive"     => "sometimes|nullable|string",
            "city"          => "sometimes|nullable|string",
            "parish"        => "sometimes|nullable|string"
        ])->validate();

        if ($request->has('first_name')) {
            $member->first_name = $request->first_name;
        }

        if ($request->has('last_name')) {
            $member->last_name = $request->last_name;
        }

        if ($request->has('email')) {
            $member->email = $request->email;
        }

        if ($request->has('phone')) {
            $member->phone = $request->phone;
        }

        if ($request->has('city')) {
            $member->city = $request->city;
        }

        if ($request->has('parish')) {
            $member->parish = $request->parish;
        }

        if ($request->has('stateOfLive')) {
            $stateOfLive = StateOfLive::where('name', $request->stateOfLive)->first();
            if ($stateOfLive) {
                $member->stateOfLive_id = $stateOfLive->id;
            } else {
                $member->stateOfLive_id = null;
            }
        }

        $member->save();

        return response()->json(['statut' => 'success', 'message' => 'Member updated successfully', 'data' => $member], 200);
    }
}
