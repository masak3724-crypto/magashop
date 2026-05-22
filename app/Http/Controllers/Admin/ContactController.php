<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        if ($request->get('filter') === 'unread') {
            $query->where('is_processed', false);
        }

        $messages = $query->paginate(15)->withQueryString();

        return view('admin.contacts.index', compact('messages'));
    }

    public function toggle(ContactMessage $message)
    {
        $message->update(['is_processed' => ! $message->is_processed]);

        return back()->with('success', 'Статус сообщения изменён.');
    }
}
