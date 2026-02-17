<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    
    public function index(Request $request)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();
        
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $company
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $logoUrl = null;
        $thumbnailUrl = null;

        if ($request->hasFile('logo')) {
            $filenameLogo = 'logo-' . Str::slug($request->name) . "-" . Str::random(5) . '.' . $request->file('logo')->getClientOriginalExtension();
            $logoUrl = $request->file('logo')->storeAs(
                'assets/logo',
                $filenameLogo,
                'public'
            );
        }

        if ($request->hasFile('thumbnail')) {
            $filenameThumbnail = 'thumbnail-' . Str::slug($request->name) . "-" . Str::random(5) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
            $thumbnailUrl = $request->file('thumbnail')->storeAs(
                'assets/thumbnail',
                $filenameThumbnail,
                'public'
            );
        }

        $company = Company::create([
            "user_id" => $request->user()->id,
            "name" => $request->name,
            "slug" => Str::slug($request->name . '-' . Str::random(5)),
            "website" => $request->website,
            "industry" => $request->industry,
            "location" => $request->location,
            "description" => $request->description,
            "logo_url" => $logoUrl,
            "thumbnail_url" => $thumbnailUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => $company
        ], 201);
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $company
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        if ($request->hasFile('logo')) {
            $filenameLogo = 'logo-' . Str::slug($request->name) . "-" . Str::random(5) . '.' . $request->file('logo')->getClientOriginalExtension();
            if ($company->logo_url) {
                Storage::disk('public')->delete($company->logo_url);
            }
            $logo = $request->file('logo')->storeAs(
                'assets/logo',
                $filenameLogo,
                'public'
            );
        } else {
            $logo = $company->logo_url;
        }

        if ($request->hasFile('thumbnail')) {
            $filenameThumbnail = 'thumbnail-' . Str::slug($request->name) . "-" . Str::random(5) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
            if ($company->thumbnail_url) {
                Storage::disk('public')->delete($company->thumbnail_url);
            }
            $thumbnail = $request->file('thumbnail')->storeAs(
                'assets/thumbnail',
                $filenameThumbnail,
                'public'
            );
        } else {
            $thumbnail = $company->thumbnail_url;
        }

        $company->update([
            "name" => $request->name,
            "slug" => Str::slug($request->name . '-' . Str::random(5)),
            "website" => $request->website,
            "industry" => $request->industry,
            "location" => $request->location,
            "description" => $request->description,
            "logo_url" => $logo,
            "thumbnail_url" => $thumbnail,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company
        ], 200);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        // Delete files
        if ($company->logo_url) {
            Storage::disk('public')->delete($company->logo_url);
        }
        if ($company->thumbnail_url) {
            Storage::disk('public')->delete($company->thumbnail_url);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully'
        ], 200);
    }
}