<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-800">{{ __('Course Attendance') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Suivi des présences de vos élèves pour vos classes et cours assignés.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ Auth::user()->name }} ({{ __('Professor') }})
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Bar for Teacher -->
            <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm">
                <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="prof_class_id" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            {{ __('Class') }}
                        </label>
                        <select id="prof_class_id" name="class_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                            @forelse($classes as $c)
                                <option value="{{ $c->id }}" {{ (string)$selectedClassId === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @empty
                                <option value="">{{ __('No assigned classes') }}</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="prof_attendance_date" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ __('Date') }}
                        </label>
                        <input type="date" id="prof_attendance_date" name="attendance_date" value="{{ $attendanceDate }}" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                    </div>

                    <div>
                        <label for="prof_subject_id" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            {{ __('Matière / Cours') }}
                        </label>
                        <select id="prof_subject_id" name="subject_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                            <option value="">{{ __('Séance générale / Matière par défaut') }}</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ (string)$selectedSubjectId === (string)$s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 rounded-xl bg-emerald-700 py-2.5 px-4 text-xs font-bold text-white hover:bg-emerald-800 transition shadow-sm active:scale-95">
                            {{ __('Afficher la liste') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Stats Bar: Present (Green), Absent (Red), Late (Yellow) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Total Students -->
                <div class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500">{{ __('Total Élèves') }}</span>
                        <p class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalCount }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm">
                        {{ $totalCount }}
                    </div>
                </div>

                <!-- Present (Green) -->
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200/90 p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            {{ __('Présents') }}
                        </span>
                        <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $presentCount }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                        ✓
                    </div>
                </div>

                <!-- Absent (Red) -->
                <div class="rounded-2xl bg-rose-50 border border-rose-200/90 p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-800 flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                            {{ __('Absents') }}
                        </span>
                        <p class="text-2xl font-black text-rose-700 mt-0.5">{{ $absentCount }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                        ✗
                    </div>
                </div>

                <!-- Late (Yellow) -->
                <div class="rounded-2xl bg-amber-50 border border-amber-200/90 p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-800 flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            {{ __('En retard') }}
                        </span>
                        <p class="text-2xl font-black text-amber-700 mt-0.5">{{ $lateCount }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-base shadow-sm">
                        ⏰
                    </div>
                </div>
            </div>

            <!-- Attendance Roll Form / List -->
            @can('attendance.manage')
            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
                <input type="hidden" name="target_type" value="student">
                <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
            @endcan

            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span>{{ __('Liste des présences des élèves') }}</span>
                            @if($selectedClassId && $classes->find($selectedClassId))
                                <span class="rounded-md bg-emerald-100 text-emerald-800 px-2 py-0.5 text-xs font-semibold">
                                    {{ $classes->find($selectedClassId)?->name }}
                                </span>
                            @endif
                            <span class="text-slate-400 font-normal">·</span>
                            <span class="text-xs font-semibold text-slate-600">
                                {{ \Illuminate\Support\Carbon::parse($attendanceDate)->locale(app()->getLocale())->translatedFormat('l d F Y') }}
                            </span>
                        </h3>

                    </div>

                    @can('attendance.manage')
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="setAllStatus('present')" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition active:scale-95">
                            {{ __('Tous Présents') }}
                        </button>
                        <button type="button" onclick="setAllStatus('absent')" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition active:scale-95">
                            {{ __('Tous Absents') }}
                        </button>
                    </div>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="py-3 px-6">{{ __('Élève') }}</th>
                                <th class="py-3 px-4">{{ __('Matricule') }}</th>
                                <th class="py-3 px-6 text-center">{{ __('État de présence') }}</th>
                                @can('attendance.manage')
                                    <th class="py-3 px-6 text-center">{{ __('Modifier le statut') }}</th>
                                    <th class="py-3 px-4">{{ __('Remarque / Heure') }}</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($teacherStudentsList as $index => $item)
                                @php($curStatus = $item['status'])
                                <tr class="hover:bg-slate-50/80 transition {{ $curStatus === 'absent' ? 'bg-rose-50/20' : ($curStatus === 'late' ? 'bg-amber-50/20' : ($curStatus === 'present' ? 'bg-emerald-50/20' : '')) }}">
                                    <!-- Élève Avatar & Nom -->
                                    <td class="py-3.5 px-6 font-medium text-slate-900">
                                        <div class="flex items-center gap-3">
                                            @if($item['photo_url'])
                                                <img src="{{ $item['photo_url'] }}" alt="" class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-100">
                                            @else
                                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs ring-2 ring-emerald-50">
                                                    {{ strtoupper(substr($item['first_name'], 0, 1) . substr($item['last_name'], 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="font-bold text-slate-800">{{ $item['name'] }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Matricule -->
                                    <td class="py-3.5 px-4 font-mono text-slate-500 font-medium">
                                        {{ $item['student_number'] }}
                                    </td>

                                    <!-- État de présence avec couleur explicite demandée -->
                                    <td class="py-3.5 px-6 text-center">
                                        @if($curStatus === 'present')
                                            <!-- VERT: Présent -->
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 border border-emerald-300 px-3.5 py-1 text-xs font-bold text-emerald-800 shadow-sm">
                                                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                                {{ __('Présent') }}
                                            </span>
                                        @elseif($curStatus === 'absent')
                                            <!-- ROUGE: Absent -->
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 border border-rose-300 px-3.5 py-1 text-xs font-bold text-rose-800 shadow-sm">
                                                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                                                {{ __('Absent') }}
                                            </span>
                                        @elseif($curStatus === 'late')
                                            <!-- JAUNE: Retard -->
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 border border-amber-300 px-3.5 py-1 text-xs font-bold text-amber-800 shadow-sm">
                                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                {{ __('En retard') }}
                                            </span>
                                        @else
                                            <!-- Non enregistré -->
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-500">
                                                {{ __('Non renseigné') }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Sélecteur rapide de statut pour le Professeur -->
                                    @can('attendance.manage')
                                    <td class="py-3.5 px-6 text-center">
                                        <input type="hidden" name="attendances[{{ $index }}][id]" value="{{ $item['id'] }}">
                                        <div class="inline-flex items-center rounded-xl bg-slate-100 p-1 gap-1 border border-slate-200 shadow-inner">
                                            <!-- Option Présent (Vert) -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="present" class="hidden peer status-radio-present" @checked($curStatus === 'present' || $curStatus === null)>
                                                <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold text-slate-600 transition peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-sm hover:text-emerald-700">
                                                    ✓ {{ __('Présent') }}
                                                </span>
                                            </label>

                                            <!-- Option Absent (Rouge) -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="absent" class="hidden peer status-radio-absent" @checked($curStatus === 'absent')>
                                                <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold text-slate-600 transition peer-checked:bg-rose-600 peer-checked:text-white peer-checked:shadow-sm hover:text-rose-700">
                                                    ✗ {{ __('Absent') }}
                                                </span>
                                            </label>

                                            <!-- Option Retard (Jaune) -->
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="late" class="hidden peer status-radio-late" @checked($curStatus === 'late')>
                                                <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold text-slate-600 transition peer-checked:bg-amber-500 peer-checked:text-white peer-checked:shadow-sm hover:text-amber-700">
                                                    ⏰ {{ __('Retard') }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>

                                    <!-- Note / Remarque -->
                                    <td class="py-3.5 px-4">
                                        <input type="text" name="attendances[{{ $index }}][note]" value="{{ $item['note'] }}" placeholder="{{ __('Remarque facultative...') }}" class="w-full rounded-lg border-slate-200 text-xs py-1 px-2.5 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-xs text-slate-400">
                                        {{ __('Aucun élève trouvé dans cette classe.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('attendance.manage')
                @if($teacherStudentsList->isNotEmpty())
                    <div class="border-t border-slate-100 bg-slate-50/70 p-4 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $totalCount }} {{ __('élèves affichés pour cette séance.') }}
                        </span>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-emerald-800 transition active:scale-95">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ __('Enregistrer les présences du cours') }}</span>
                        </button>
                    </div>
                @endif
                @endcan
            </div>

            @can('attendance.manage')
            </form>
            @endcan

        </div>
    </div>

    @can('attendance.manage')
    <script>
        function setAllStatus(status) {
            if (status === 'present') {
                document.querySelectorAll('.status-radio-present').forEach(r => r.checked = true);
            } else if (status === 'absent') {
                document.querySelectorAll('.status-radio-absent').forEach(r => r.checked = true);
            } else if (status === 'late') {
                document.querySelectorAll('.status-radio-late').forEach(r => r.checked = true);
            }
        }
    </script>
    @endcan
</x-app-layout>
