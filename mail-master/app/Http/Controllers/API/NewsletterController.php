<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $newsletters = Newsletter::where('user_id', $request->user()->id)
            ->when($request->has('search'), function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($newsletters);
    }
   

   

 

  
}