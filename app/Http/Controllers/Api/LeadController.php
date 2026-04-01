<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Lead;
class LeadController extends Controller
{
    /**
     * Store a new lead in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeLead(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|string|max:10',
                'father_name' => 'required|string|max:255',
                'dob' => 'required|date',
                'gender' => 'required|string|max:255',
                'emergency_contact' => 'required|string|max:255',
                'blood_group' => 'required|string|max:255'
               
            ], [
                'name.required' => 'Name is required',
                'name.string' => 'Name must be a string',
                'name.max' => 'Name must not exceed 255 characters',
                'mobile.required' => 'Mobile number is required',
                'mobile.string' => 'Mobile number must be a string',
                'mobile.max' => 'Mobile number must not exceed 10 characters',
                'father_name.required' => 'Father name is required',
                'father_name.string' => 'Father name must be a string',
                'father_name.max' => 'Father name must not exceed 255 characters',
                'dob.required' => 'Date of birth is required',
                'dob.date' => 'Date of birth must be a date',
                'gender.required' => 'Gender is required',
                'gender.string' => 'Gender must be a string',
                'gender.max' => 'Gender must not exceed 255 characters',
                'emergency_contact.required' => 'Emergency contact is required',
                'emergency_contact.string' => 'Emergency contact must be a string',
                'emergency_contact.max' => 'Emergency contact must not exceed 255 characters',
                'blood_group.required' => 'Blood group is required',
            ]);
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImageName = time() . '_profile_' . $profileImage->getClientOriginalName();
                $profileImage->move(public_path('uploads'), $profileImageName);
                $request->merge(['profile_img' => 'uploads/' . $profileImageName]);
            }
            $lead = Lead::create($request->except('profile_image'));
            return response()->json([
                'success' => true,
                'message' => 'Lead stored successfully',
		'lead_id' => $lead->id,
                'data' => ['lead_id' => $lead->id]
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $firstError = collect($e->errors())->first()[0];
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors' => null
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to store lead',
                'error' => $e->getMessage()
            ], 500);
        } 
    }
    /**
     * Update the specified lead in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLead(Request $request, $id)
    {
        if(!$id){
            return response()->json([
                'success' => false,
                'message' => 'Lead ID is required',
                'error' => null
            ], 400);
        }
        try {
            $lead = Lead::findOrFail($id);
                // Store front Aadhar image in uploads folder
            if ($request->hasFile('front_addhar_image')) {
                $frontAadharFile = $request->file('front_addhar_image');
                $frontAadharName = time() . '_front_' . $frontAadharFile->getClientOriginalName();
                $frontAadharFile->move(public_path('uploads'), $frontAadharName);
                $request->merge(['front_addhar_img' => 'uploads/' . $frontAadharName]);
            }
            if ($request->hasFile('back_addhar_image')) {
                $backAadharFile = $request->file('back_addhar_image');
                $backAadharName = time() . '_back_' . $backAadharFile->getClientOriginalName();
                $backAadharFile->move(public_path('uploads'), $backAadharName);
                $request->merge(['back_addhar_img' => 'uploads/' . $backAadharName]);
            }
            if ($request->hasFile('pan_image')) {
                $panFile = $request->file('pan_image');
                $panName = time() . '_pan_' . $panFile->getClientOriginalName();
                $panFile->move(public_path('uploads'), $panName);
                $request->merge(['pan_img' => 'uploads/' . $panName]);
            }
              // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImageName = time() . '_profile_' . $profileImage->getClientOriginalName();
                $profileImage->move(public_path('uploads'), $profileImageName);
                $request->merge(['profile_img' => 'uploads/' . $profileImageName]);
            }
            $requestData = $request->except(['front_addhar_image', 'back_addhar_image', 'profile_image','pan_image']);
            $lead->update($requestData);
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'data' => ['lead_id' => $lead->id]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
                'error' => 'Lead with ID ' . $id . ' does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}