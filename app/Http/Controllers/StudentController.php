<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;


class StudentController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
            'marks' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::where('name', $request->name)
            ->where('subject_name', $request->subject_name)
            ->first();

        if ($student) {
            $student->marks += $request->marks;
            $student->save();
            return response()->json(['message' => 'Student updated successfully!', 'student' => $student], 200);
        } else {
            $student = Student::create([
                'name' => $request->name,
                'subject_name' => $request->subject_name,
                'marks' => $request->marks,
            ]);
            return response()->json(['message' => 'Student added successfully!', 'student' => $student], 201);
        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
            'marks' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::find($id);
        if ($student) {
            $student->name = $request->name;
            $student->subject_name = $request->subject_name;
            $student->marks = $request->marks;
            $student->save();

            return response()->json(['message' => 'Student updated successfully!'], 200);
        }

        return response()->json(['message' => 'Student not found.'], 404);
    }

    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully!'], 200);
    }
}
