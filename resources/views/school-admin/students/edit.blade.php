@extends('layouts.app')

@section('page_title', 'Edit Student Registration')

@section('content')
{{-- Back Link --}}
<div class="mb-6">
    <a href="{{ route('school.students.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-100 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        Back to Students
    </a>
</div>

{{-- Rejection Remarks Banner --}}
@if($student->status === 'Rejected' && $student->remarks)
<div class="mb-6 p-4 rounded-2xl bg-rose-950/40 border border-rose-800/40 text-rose-200 shadow-[0_0_15px_rgba(244,63,94,0.05)]">
    <div class="flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
        <div>
            <h4 class="text-sm font-semibold text-rose-300">Registration Rejected by Board Admin</h4>
            <p class="text-xs text-rose-200/90 mt-1 leading-relaxed"><strong class="font-semibold text-rose-200">Remarks:</strong> {{ $student->remarks }}</p>
            <p class="text-[10px] text-rose-400 mt-2">Please correct the information below and re-submit the application.</p>
        </div>
    </div>
</div>
@endif

{{-- Form Container --}}
<form method="POST" action="{{ route('school.students.update', $student) }}" enctype="multipart/form-data" class="space-y-6" x-data="{ photoPreview: '{{ $student->photo_url }}' }">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Personal Information Card --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/60 pb-3 flex items-center gap-2 text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200">Personal Information</h3>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Student Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $student->name) }}" required placeholder="Enter student's full name"
                       class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Gender <span class="text-rose-500">*</span></label>
                    <select name="gender" required class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        <option value="">Select Gender</option>
                        <option value="Male" @selected(old('gender', $student->gender) === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender', $student->gender) === 'Female')>Female</option>
                        <option value="Other" @selected(old('gender', $student->gender) === 'Other')>Other</option>
                    </select>
                    @error('gender') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Date of Birth <span class="text-rose-500">*</span></label>
                    <input type="date" name="dob" value="{{ old('dob', $student->dob->format('Y-m-d')) }}" required max="{{ date('Y-m-d') }}"
                           class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    @error('dob') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Father's Name <span class="text-rose-500">*</span></label>
                <input type="text" name="father_name" value="{{ old('father_name', $student->father_name) }}" required placeholder="Enter father's full name"
                       class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                @error('father_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Mother's Name <span class="text-rose-500">*</span></label>
                <input type="text" name="mother_name" value="{{ old('mother_name', $student->mother_name) }}" required placeholder="Enter mother's full name"
                       class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                @error('mother_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Mobile Number <span class="text-rose-500">*</span></label>
                <input type="tel" name="mobile_number" value="{{ old('mobile_number', $student->mobile_number) }}" required placeholder="10-digit mobile number"
                       class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                @error('mobile_number') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Academic & Examination Info Card --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/60 pb-3 flex items-center gap-2 text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.231-4.41 60.46 60.46 0 00-.49-6.347m-15.482 0a48.69 48.69 0 0115.482 0m-15.482 0L12 14.197 19.74 10.15m-16.5 3.03h.008v.008H3.24v-.008zm16.5 0h.008v.008h-.008v-.008z" /></svg>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200">Academic & Examination Details</h3>
            </div>



            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Class <span class="text-rose-500">*</span></label>
                    <select name="class_id" required class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('class_id', $student->class_id) == $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Category <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $student->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Examination Session <span class="text-rose-500">*</span></label>
                <select name="examination_id" required class="w-full bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    <option value="">Select Examination Session</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}" @selected(old('examination_id', $student->examination_id) == $exam->id)>{{ $exam->name }}</option>
                    @endforeach
                </select>
                @error('examination_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Candidate Photograph</label>
                <div class="flex items-center gap-4 mt-1.5">
                    <div class="w-16 h-16 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden shrink-0">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!photoPreview">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-500"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
                        </template>
                    </div>
                    <div class="flex-1">
                        <input type="file" x-ref="photoInput" name="photograph" accept="image/jpeg,image/png,image/jpg"
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }"
                               class="hidden">
                        <button type="button" @click="$refs.photoInput.click()"
                            class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold cursor-pointer transition-all flex items-center gap-2 shadow-lg shadow-indigo-600/10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload Photo
                        </button>
                        <p class="text-[10px] text-slate-500 mt-2">Max size: 3MB. Formats: JPEG, JPG, PNG. Leave empty to keep current photo.</p>
                    </div>
                </div>
                @error('photograph') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Form Buttons --}}
    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-800/60">
        <a href="{{ route('school.students.index') }}" 
           class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl transition-all">
            Cancel
        </a>
        <button type="submit" 
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
            Update Registration
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script @nonce>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.querySelector('select[name="category_id"]');
    const classSelect = document.querySelector('select[name="class_id"]');
    
    if (!categorySelect || !classSelect) return;

    // Save the original class options
    const originalOptions = Array.from(classSelect.options).map(opt => ({
        value: opt.value,
        text: opt.text
    }));

    function filterClasses() {
        const selectedCategoryOption = categorySelect.options[categorySelect.selectedIndex];
        if (!selectedCategoryOption) return;

        const categoryText = (selectedCategoryOption.text || '').toLowerCase().trim();
        
        let allowedClassNumbers = null;

        if (categoryText.includes('rainbow3') || categoryText === 'rainbow 3' || categoryText.includes('rainbow 3')) {
            allowedClassNumbers = ['3'];
        } else if (categoryText.includes('rainbow4') || categoryText === 'rainbow 4' || categoryText.includes('rainbow 4')) {
            allowedClassNumbers = ['4'];
        } else if (categoryText.includes('rainbow5') || categoryText === 'rainbow 5' || categoryText.includes('rainbow 5')) {
            allowedClassNumbers = ['5'];
        } else if (categoryText.includes('planet')) {
            allowedClassNumbers = ['6', '7', '8'];
        } else if (categoryText.includes('galaxy hs') && !categoryText.includes('galaxy hss')) {
            allowedClassNumbers = ['9', '10'];
        } else if (categoryText.includes('galaxy hss')) {
            allowedClassNumbers = ['11', '12'];
        }

        // Store currently selected class value
        const currentSelectedValue = classSelect.value;

        // Clear existing options except the placeholder
        classSelect.innerHTML = '';
        
        const placeholderOpt = document.createElement('option');
        placeholderOpt.value = '';
        placeholderOpt.textContent = 'Select Class';
        classSelect.appendChild(placeholderOpt);

        const filteredOptions = [];

        originalOptions.forEach(opt => {
            if (opt.value === '') return; // skip placeholder

            let matches = false;
            if (!allowedClassNumbers) {
                matches = true;
            } else {
                const classText = opt.text.toLowerCase();
                const digits = classText.match(/\d+/g) || [];
                matches = allowedClassNumbers.some(num => digits.includes(num));
            }

            if (matches) {
                filteredOptions.push(opt);
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.textContent = opt.text;
                classSelect.appendChild(newOpt);
            }
        });

        // Set selection
        if (allowedClassNumbers && filteredOptions.length === 1) {
            classSelect.value = filteredOptions[0].value;
        } else {
            const stillExists = filteredOptions.some(opt => opt.value === currentSelectedValue);
            if (stillExists) {
                classSelect.value = currentSelectedValue;
            } else {
                classSelect.value = '';
            }
        }
    }

    categorySelect.addEventListener('change', filterClasses);
    filterClasses();
});
</script>
@endpush

