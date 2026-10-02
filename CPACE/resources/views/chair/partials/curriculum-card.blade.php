{{-- Curriculum card: which curriculum, where its topics came from, what's next, and its few actions.
     Shown beside the subjects (see subjects.blade.php); relies on that page's variables. --}}
@if($version)
        <aside class="cur-aside">
        @php
            $stateText = $version->isDraft() ? 'Draft' : ($version->isArchived() ? 'Archived' : 'Active');
            $topicTotal = $subjects->sum(fn ($s) => $s->topics->count());
            $emptyCount = $subjects->filter(fn ($s) => $s->topics->isEmpty())->count();
            $stepImportDone = $topicTotal > 0;
            $stepReviewDone = $topicTotal > 0 && $emptyCount === 0;
        @endphp
        <div class="cur-card is-{{ $version->status }}">
            <div class="cur-row">
                <span class="cur-ic"><i class="fas fa-book-bookmark"></i></span>
                <div class="cur-info">
                    <div class="cur-eyebrow">Curriculum you are viewing</div>
                    <div class="cur-name">{{ $version->label }} <span class="curr-pill {{ $version->status }}">{{ $stateText }}</span></div>
                    <div class="cur-sub">
                        @if($version->effectiveRange()) For batches {{ $version->effectiveRange() }} @else No batch range set @endif
                        &bull; {{ number_format($topicTotal) }} topic{{ $topicTotal === 1 ? '' : 's' }} in {{ $subjects->count() }} subject{{ $subjects->count() === 1 ? '' : 's' }}
                        @if($version->published_at) &bull; published {{ $version->published_at->format('M j, Y') }} @endif
                    </div>
                </div>
                <div class="cur-actions">
                    <select class="curr-select" aria-label="Curriculum version" title="Switch curriculum" onchange="window.location = '{{ route('chair.subjects') }}?version=' + encodeURIComponent(this.value)">
                        @foreach($versions as $v)
                            <option value="{{ $v->id }}" @selected($v->id === $version->id)>
                                {{ $v->label }} — {{ ucfirst($v->status) }}{{ $v->effectiveRange() ? ' (' . $v->effectiveRange() . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if($version->isDraft())
                        @if($stepImportDone)
                            <button type="button" class="btn btn-primary" onclick="publishDraft()"><i class="fas fa-rocket"></i> Publish</button>
                        @else
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('tosModal').classList.add('open')"><i class="fas fa-file-import"></i> Import TOS</button>
                        @endif
                    @elseif(! $hasDraft)
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('curriculumModal').classList.add('open')"><i class="fas fa-plus"></i> New curriculum</button>
                    @endif
                    <div class="cur-menu">
                        <button type="button" class="cur-dots" onclick="toggleCurMenu(event, this)" aria-label="More curriculum actions"><i class="fas fa-ellipsis"></i></button>
                        <div class="cur-dropdown">
                            @unless($readOnly)
                                <button type="button" onclick="document.getElementById('tosModal').classList.add('open')"><i class="fas fa-file-import"></i> {{ $stepImportDone ? 'Import another TOS file' : 'Import TOS file' }}</button>
                            @endunless
                            <button type="button" onclick="document.getElementById('historyModal').classList.add('open')"><i class="fas fa-clock-rotate-left"></i> View change history</button>
                            @if($version->isDraft())
                                <button type="button" onclick="document.getElementById('curriculumEditModal').classList.add('open')"><i class="fas fa-pen"></i> Edit name &amp; batches</button>
                                <button type="button" class="danger" onclick="discardDraft()"><i class="fas fa-trash"></i> Discard this draft</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="cur-help">
                @if($pendingImport)
                    <div class="cur-alert">
                        <i class="fas fa-hourglass-half"></i>
                        <span><strong>A TOS file is waiting for your review:</strong> {{ $pendingImport->original_filename }}. Nothing is added to the curriculum until you confirm it.</span>
                        <a class="cur-alert-btn" href="{{ route('chair.curriculum.import.review', $pendingImport->id) }}">Continue review</a>
                    </div>
                @endif

                @if($version->isDraft())
                    <div class="cur-steps">
                        <div class="cur-step {{ $stepImportDone ? 'done' : 'now' }}"><span class="n">{{ $stepImportDone ? '✓' : '1' }}</span><span><strong>Add topics</strong><small>Import the TOS file, or add topics by hand</small></span></div>
                        <div class="cur-step {{ $stepReviewDone ? 'done' : ($stepImportDone ? 'now' : '') }}"><span class="n">{{ $stepReviewDone ? '✓' : '2' }}</span><span><strong>Check each subject</strong><small>{{ $emptyCount > 0 && $stepImportDone ? $emptyCount . ' subject' . ($emptyCount === 1 ? ' has' : 's have') . ' no topics yet' : 'Open a subject below to edit its topics' }}</small></span></div>
                        <div class="cur-step {{ $stepReviewDone ? 'now' : '' }}"><span class="n">3</span><span><strong>Publish</strong><small>Students only see it after this</small></span></div>
                    </div>
                @elseif($version->isArchived())
                    <p class="cur-text"><i class="fas fa-lock"></i> <strong>Archived — view only.</strong> Kept as history; past quiz results and questions under it are preserved.</p>
                @else
                    <p class="cur-text"><i class="fas fa-circle-check"></i> <strong>This is the curriculum students are studying now.</strong> Topics are stored inside each subject — open a subject below to see or edit them. For the next batch, start a new curriculum instead of changing this one.</p>
                @endif

                @if($lastImport)
                    <p class="cur-source"><i class="fas fa-file-pdf"></i> TOS file: <strong>{{ $lastImport->original_filename }}</strong> &bull; imported {{ $lastImport->updated_at?->format('M j, Y') }}</p>
                @endif

            </div>
        </div>

        <div class="modal-overlay" id="historyModal" onclick="if (event.target === this) closeModal('historyModal')">
            <div class="modal" style="max-width:560px;">
                <h3>Change history</h3>
                <div class="modal-sub">{{ $version->label }} — who changed what, newest first.</div>
                @if($audits->isNotEmpty())
                    <ul class="audit-list audit-modal">
                        @foreach($audits as $audit)
                            <li>
                                <span class="audit-when">{{ $audit->created_at?->format('M j, g:i A') }}</span>
                                <span><strong>{{ $audit->user?->name ?? 'System' }}</strong> · {{ $audit->label() }}@if($audit->subject) ({{ $audit->subject->code }})@endif @if($audit->details)— {{ $audit->details }}@endif</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-msg"><i class="fas fa-clock-rotate-left"></i>No changes recorded for this curriculum yet.</div>
                @endif
                <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('historyModal')">Close</button></div>
            </div>
        </div>
        </aside>
@endif
