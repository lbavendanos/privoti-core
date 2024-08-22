<?php

namespace App\Domains\D\Http\Controllers\Auth;

use App\Domains\D\Http\Controllers\Controller;
use App\Domains\D\Http\Resources\AdminResource;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return new AdminResource($request->user());
    }
}
