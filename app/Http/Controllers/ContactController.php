<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\ContactUpdated;

class ContactController extends Controller
{
    public function index()
    {
        return response()->json(Contact::all(), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts',
            'phone_number' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        sleep(20); // Simulate slow network/server load

        $contact = Contact::create($request->all());

        ContactHistory::create([
            'contact_id' => $contact->id,
            'action' => 'created',
        ]);

        return response()->json($contact, 201);
    }

    public function show($id)
    {
        $contact = Contact::find($id);
        if (is_null($contact)) {
            return response()->json(['message' => 'Contact Not Found'], 404);
        }
        return response()->json($contact, 200);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);
        if (is_null($contact)) {
            return response()->json(['message' => 'Contact Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts,email,' . $id,
            'phone_number' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $original = $contact->getOriginal();
        $changes = $request->all();

        $contact->update($request->all());

        event(new ContactUpdated($contact));

        ContactHistory::create([
            'contact_id' => $contact->id,
            'action' => 'updated',
            'changes' => json_encode(array_diff_assoc($original, $changes)),
        ]);

        return response()->json($contact, 200);
    }

    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (is_null($contact)) {
            return response()->json(['message' => 'Contact Not Found'], 404);
        }

        ContactHistory::create([
            'contact_id' => $contact->id,
            'action' => 'deleted',
        ]);

        $contact->delete();
        return response()->json(null, 204);
    }

    public function history($id)
    {
        $contact = Contact::find($id);
        if (is_null($contact)) {
            return response()->json(['message' => 'Contact Not Found'], 404);
        }

        $history = ContactHistory::where('contact_id', $id)->get();
        return response()->json($history, 200);
    }
}