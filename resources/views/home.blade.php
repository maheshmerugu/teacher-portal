@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('All Students') }}</div>

                    <div class="card-body">
                        @if ($students->isEmpty())
                            <p>No students found.</p>
                        @else
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Subject</th>
                                        <th>Marks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td class="editable" data-field="name" data-id="{{ $student->id }}">
                                                {{ $student->name }}</td>
                                            <td class="editable" data-field="subject_name" data-id="{{ $student->id }}">
                                                {{ $student->subject_name }}</td>
                                            <td class="editable" data-field="marks" data-id="{{ $student->id }}">
                                                {{ $student->marks }}</td>
                                            <td>
                                                <button class="btn btn-warning edit-student"
                                                    data-id="{{ $student->id }}">Edit</button>
                                                <button class="btn btn-danger delete-student"
                                                    data-id="{{ $student->id }}">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>


                            </table>
                        @endif
                        <button class="btn btn-primary" id="addStudentButton" data-toggle="modal" data-target="#addStudentModal">Add
                            Student</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="form-group">
                            <label for="student-name">Student Name</label>
                            <input type="text" class="form-control" id="student-name" name="student_name" required>
                        </div>
                        <div class="form-group">
                            <label for="subject-name">Subject Name</label>
                            <input type="text" class="form-control" id="subject-name" name="subject_name" required>
                        </div>
                        <div class="form-group">
                            <label for="marks">Marks</label>
                            <input type="number" class="form-control" id="marks" name="marks" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>

function toggleAddStudentButton(disable) {
        const addStudentButton = document.getElementById('addStudentButton');
        addStudentButton.disabled = disable; 
    }



        document.getElementById('addStudentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const studentName = document.getElementById('student-name').value;
            const subjectName = document.getElementById('subject-name').value;
            const marks = document.getElementById('marks').value;

            fetch('/add/student', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    body: JSON.stringify({
                        name: studentName,
                        subject_name: subjectName,
                        marks: marks
                    }),
                })
                .then(response => response.json())
                .then(data => {

                    if (data.message) {
                        toastr.success(data.message);
                        setTimeout(() => {
                            location.reload(); // Reload the page after 2 seconds
                        }, 2000);
                    } else if (data.errors) {
                        Object.values(data.errors).forEach(error => {
                            toastr.error(error[0]);
                        });
                    } else {
                        toastr.error('Error adding student. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('An unexpected error occurred.');
                });
        });

        $(document).ready(function() {


            let isEditing = false; 

            $('.edit-student').on('click', function() {

                toggleAddStudentButton(true);


                const studentId = $(this).data('id');
                const row = $(this).closest('tr');

                if (!isEditing) {
                    row.find('.editable').attr('contenteditable', 'true').addClass('edit-mode');
                    $(this).text('Save');
                } else {
                    const updatedData = {
                        id: studentId,
                        name: row.find('td[data-field="name"]').text().trim(),
                        subject_name: row.find('td[data-field="subject_name"]').text().trim(),
                        marks: parseInt(row.find('td[data-field="marks"]').text().trim()),
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    };

                    if (!updatedData.name || !updatedData.subject_name || isNaN(updatedData.marks)) {
                        toastr.error('Please fill in all fields correctly.');
                        return;
                    }

                    $.ajax({
                        type: 'PUT',
                        url: '/students/' + studentId,
                        data: JSON.stringify(updatedData),
                        contentType: 'application/json',
                        success: function(response) {
                            toastr.success(response.message || 'Student updated successfully!');
                            setTimeout(() => {
                                location.reload(); 
                            }, 2000);
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error updating student. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMessage = xhr.responseJSON.errors.name ? xhr.responseJSON
                                    .errors.name[0] : errorMessage;
                            }
                            toastr.error(errorMessage);
                        }
                    });
                }

                isEditing = !isEditing;
            });

            $('.editable').on('focusout', function() {
                if (isEditing) {
                    $(this).attr('contenteditable', 'false').removeClass('edit-mode');
                }
            });
        })
        document.querySelectorAll('.delete-student').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');

                if (confirm('Are you sure you want to delete this student?')) {
                    fetch(`/students/${studentId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                            }
                        })
                        .then(response => {
                            return response.json().then(data => ({
                                status: response.status,
                                body: data
                            }));
                        })
                        .then(({
                            status,
                            body
                        }) => {
                            if (status === 200) {
                                toastr.success(body.message); 
                                setTimeout(() => {
                                    location.reload(); 
                                }, 2000);
                            } else {
                                toastr.error(body.message ||
                                    'Error deleting student. Please try again.'); 
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toastr.error('An unexpected error occurred.'); 
                        });
                }
            });
        });
    </script>












@endsection
