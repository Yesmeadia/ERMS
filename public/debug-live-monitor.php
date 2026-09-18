<?php
/**
 * Live Monitor Debugger
 * =====================
 * PUBLIC standalone debugger — NO Laravel auth required.
 * Access via: http://127.0.0.1:8000/debug-live-monitor.php?exam_id=4
 *
 * HOW TO USE:
 *   Copy this file to:  c:\laragon\www\exam\public\debug-live-monitor.php
 *   Then open in browser with your exam ID.
 *   REMOVE THIS FILE AFTER DEBUGGING.
 */

// ── Bootstrap Laravel (loads .env, DB, models) ────────────────────────────────
$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
$app    = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(\Illuminate\Http\Request::capture());

// ── Input ──────────────────────────────────────────────────────────────────────
$examId = (int) ($_GET['exam_id'] ?? 0);

// ── Colour helpers ─────────────────────────────────────────────────────────────
function pill(string $text, string $cls = 'slate'): string {
    $map = [
        'green'  => 'background:#052e16;color:#4ade80;border:1px solid #166534',
        'indigo' => 'background:#1e1b4b;color:#a5b4fc;border:1px solid #3730a3',
        'slate'  => 'background:#1e293b;color:#94a3b8;border:1px solid #334155',
        'rose'   => 'background:#2d0a0a;color:#fca5a5;border:1px solid #991b1b',
        'amber'  => 'background:#292100;color:#fcd34d;border:1px solid #92400e',
    ];
    $style = $map[$cls] ?? $map['slate'];
    return "<span style='display:inline-block;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600;{$style}'>" . htmlspecialchars($text) . "</span>";
}
function statBox(string $lbl, int $num, string $color = '#7c3aed'): string {
    return "<div style='display:inline-block;background:#0f172a;border:1px solid #334155;border-radius:10px;padding:10px 18px;margin:6px;min-width:110px;text-align:center'>
        <div style='font-size:28px;font-weight:700;color:{$color}'>{$num}</div>
        <div style='font-size:10px;color:#64748b;text-transform:uppercase;margin-top:2px'>{$lbl}</div>
    </div>";
}
function notice(string $msg, bool $warn = true): string {
    $bg  = $warn ? '#1c1007' : '#052e16';
    $brd = $warn ? '#92400e' : '#166534';
    $clr = $warn ? '#fcd34d' : '#4ade80';
    $ico = $warn ? '⚠' : '✅';
    return "<div style='background:{$bg};border:1px solid {$brd};color:{$clr};padding:10px 16px;border-radius:8px;margin-bottom:12px;font-size:12px'>{$ico} {$msg}</div>";
}

$COMPLETED = ['SUBMITTED', 'EXPIRED', 'COMPLETED'];
$ACTIVE    = ['READY', 'IN_PROGRESS', 'QUESTION_ACTIVE', 'ANSWERED', 'QUESTION_TIMEOUT'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Live Monitor Debugger</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;padding:24px;font-size:13px}
  h1{font-size:22px;font-weight:700;color:#fff;margin-bottom:4px}
  h2{font-size:14px;font-weight:600;color:#7c3aed;margin:24px 0 10px;border-bottom:1px solid #1e293b;padding-bottom:6px;text-transform:uppercase;letter-spacing:.06em}
  .card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:16px 20px;margin-bottom:16px;overflow-x:auto}
  table{width:100%;border-collapse:collapse}
  th{text-align:left;padding:7px 12px;background:#0f172a;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.05em}
  td{padding:7px 12px;border-bottom:1px solid #1e293b;vertical-align:middle}
  tr:last-child td{border-bottom:none}
  .form{display:flex;gap:10px;align-items:center;margin-bottom:24px}
  input[type=number]{background:#1e293b;border:1px solid #475569;color:#e2e8f0;padding:8px 14px;border-radius:8px;font-size:13px;width:140px;outline:none}
  button{background:#7c3aed;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600}
  button:hover{background:#6d28d9}
  .mono{font-family:'Courier New',monospace;font-size:12px}
</style>
</head>
<body>

<h1>🔍 Live Monitor Debugger</h1>
<p style="color:#64748b;margin-bottom:20px">Inspect raw database state for a Live Proctoring exam. <strong style="color:#f87171">Delete this file after debugging.</strong></p>

<form class="form" method="GET">
  <input type="number" name="exam_id" value="<?= $examId ?>" placeholder="Exam ID" min="1">
  <button type="submit">🔎 Inspect</button>
</form>

<?php if (!$examId): ?>
<?= notice('Enter an Exam ID above to begin.') ?>
<?php else:
    try {
        $exam = \App\Models\OnlineExam::find($examId);
        if (!$exam) {
            echo notice("No OnlineExam found with ID <strong>{$examId}</strong>. Check the URL in the Live Monitor admin page.");
            goto done;
        }

        $sessions = \App\Models\OnlineExamSession::where('online_exam_id', $examId)->get();
        $results  = \App\Models\OnlineExamResult::where('online_exam_id', $examId)->with('student')->get();
        $enrolled = $exam->examStudents()->with(['student.school'])->get();

        $sessionStudentIds = $sessions->pluck('student_id')->unique();
        $resultStudentIds  = $results->pluck('student_id')->unique();

        // Stats
        $sessCompleted  = $sessions->filter(fn($s) => in_array((string)$s->status, $COMPLETED))->count();
        $sessActive     = $sessions->filter(fn($s) => in_array((string)$s->status, $ACTIVE))->count();
        $sessTerminated = $sessions->filter(fn($s) => (string)$s->status === 'TERMINATED')->count();
        $noSession      = $enrolled->filter(fn($e) => !$sessionStudentIds->contains($e->student_id));
        $withResult     = $noSession->filter(fn($e) => $resultStudentIds->contains($e->student_id));
        $trueNotStarted = $noSession->filter(fn($e) => !$resultStudentIds->contains($e->student_id));
        $totalCompleted = $sessCompleted + $withResult->count();
        $totalAttended  = $sessions->filter(fn($s) => (string)$s->status !== 'NOT_STARTED')->count() + $withResult->count();

        $hasDataMismatch = $results->count() > 0 && $sessions->count() === 0;
?>

<!-- Exam info -->
<h2>📋 Exam</h2>
<div class="card">
  <table>
    <tr><td style="color:#64748b;width:180px">ID</td><td class="mono"><?= $exam->id ?></td></tr>
    <tr><td style="color:#64748b">Name</td><td style="color:#e2e8f0;font-weight:600"><?= htmlspecialchars($exam->name) ?></td></tr>
    <tr><td style="color:#64748b">Code</td><td class="mono" style="color:#818cf8"><?= htmlspecialchars($exam->code) ?></td></tr>
    <tr><td style="color:#64748b">Status</td><td><?= htmlspecialchars($exam->status ?? '-') ?></td></tr>
    <tr><td style="color:#64748b">Questions</td><td><?= $exam->total_questions ?? '-' ?></td></tr>
    <tr><td style="color:#64748b">Max Marks</td><td><?= $exam->total_marks ?? '-' ?></td></tr>
  </table>
</div>

<!-- Stats -->
<h2>📊 Computed Stats</h2>
<div class="card">
  <?php if ($hasDataMismatch): ?>
  <?= notice('<strong>Data Mismatch:</strong> ' . $results->count() . ' result(s) found but 0 sessions exist. Sessions may have been cleared. The updated controller fix reads from <code>online_exam_results</code> as fallback — students should now show as Submitted in the Live Monitor.') ?>
  <?php elseif ($results->count() > 0 && $sessions->count() > 0): ?>
  <?= notice('Sessions and results both exist. Data looks consistent.', false) ?>
  <?php elseif ($results->count() === 0 && $sessions->count() === 0): ?>
  <?= notice('No sessions and no results for this exam yet.') ?>
  <?php endif; ?>

  <?= statBox('Enrolled',     $enrolled->count(),    '#7c3aed') ?>
  <?= statBox('All Attended', $totalAttended,         '#22d3ee') ?>
  <?= statBox('Active Now',   $sessActive,            '#4ade80') ?>
  <?= statBox('Completed',    $totalCompleted,        '#818cf8') ?>
  <?= statBox('Terminated',   $sessTerminated,        '#f87171') ?>
  <?= statBox('Results (DB)', $results->count(),      '#fbbf24') ?>
  <?= statBox('Sessions(DB)', $sessions->count(),     '#94a3b8') ?>
</div>

<!-- Enrolled students -->
<h2>👥 Enrolled Students (<?= $enrolled->count() ?>)</h2>
<div class="card">
<table>
  <tr>
    <th>Stud ID</th><th>Name</th><th>Reg No</th>
    <th>Has Session?</th><th>Has Result?</th><th>Status in Monitor</th>
  </tr>
  <?php foreach ($enrolled as $es):
    $hasSess = $sessionStudentIds->contains($es->student_id);
    $hasRes  = $resultStudentIds->contains($es->student_id);
    if ($hasSess) {
        $s = $sessions->firstWhere('student_id', $es->student_id);
        $st = $s ? (string)$s->status : '?';
        $cls = in_array($st, $COMPLETED) ? 'indigo' : (in_array($st, $ACTIVE) ? 'green' : ($st === 'TERMINATED' ? 'rose' : 'slate'));
        $net = pill($st, $cls);
    } elseif ($hasRes) {
        $net = pill('SUBMITTED (result-backed)', 'indigo');
    } else {
        $net = pill('NOT STARTED', 'slate');
    }
  ?>
  <tr>
    <td class="mono"><?= $es->student_id ?></td>
    <td style="font-weight:500"><?= htmlspecialchars($es->student?->name ?? '—') ?></td>
    <td class="mono" style="color:#818cf8"><?= htmlspecialchars($es->student?->registration_number ?? '—') ?></td>
    <td><?= $hasSess ? pill('YES', 'green') : pill('NO', 'rose') ?></td>
    <td><?= $hasRes  ? pill('YES', 'green') : pill('NO', 'slate') ?></td>
    <td><?= $net ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>

<!-- Sessions -->
<h2>🗃 Sessions Table (<?= $sessions->count() ?> rows)</h2>
<div class="card">
<?php if ($sessions->isEmpty()): ?>
<?= notice('No rows found in <code>online_exam_sessions</code> for this exam.') ?>
<?php else: ?>
<table>
  <tr><th>Sess ID</th><th>Student ID</th><th>Status</th><th>Started</th><th>Completed</th><th>Violations</th><th>Heartbeat</th><th>Camera</th></tr>
  <?php foreach ($sessions as $s):
    $st  = (string)$s->status;
    $cls = in_array($st, $COMPLETED) ? 'indigo' : (in_array($st, $ACTIVE) ? 'green' : ($st === 'TERMINATED' ? 'rose' : 'slate'));
  ?>
  <tr>
    <td class="mono"><?= $s->id ?></td>
    <td><?= $s->student_id ?></td>
    <td><?= pill($st, $cls) ?></td>
    <td style="color:#94a3b8"><?= $s->exam_started_at?->format('H:i:s') ?? '—' ?></td>
    <td style="color:#94a3b8"><?= $s->completed_at?->format('H:i:s') ?? '—' ?></td>
    <td style="color:<?= $s->violations_count > 0 ? '#f87171' : '#94a3b8' ?>"><?= $s->violations_count ?></td>
    <td style="color:#94a3b8"><?= $s->last_heartbeat_at?->diffForHumans() ?? 'Never' ?></td>
    <td style="color:#94a3b8"><?= htmlspecialchars($s->camera_status ?? '—') ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<!-- Results -->
<h2>🏆 Results Table (<?= $results->count() ?> rows)</h2>
<div class="card">
<?php if ($results->isEmpty()): ?>
<?= notice('No rows found in <code>online_exam_results</code> for this exam.') ?>
<?php else: ?>
<table>
  <tr><th>Res ID</th><th>Student</th><th>Session ID</th><th>Score</th><th>Attempted</th><th>Rank</th><th>Created</th></tr>
  <?php foreach ($results as $r): ?>
  <tr>
    <td class="mono"><?= $r->id ?></td>
    <td>
      <div style="font-weight:500"><?= htmlspecialchars($r->student?->name ?? '—') ?></div>
      <div class="mono" style="color:#818cf8;font-size:11px"><?= htmlspecialchars($r->student?->registration_number ?? '—') ?></div>
    </td>
    <td class="mono" style="color:<?= $r->online_exam_session_id ? '#818cf8' : '#f87171' ?>">
      <?= $r->online_exam_session_id ?? '⚠ NULL' ?>
    </td>
    <td style="color:#4ade80;font-weight:700"><?= $r->final_score ?></td>
    <td><?= $r->total_attempted ?> / <?= $r->total_questions ?></td>
    <td style="color:#fbbf24"><?= $r->rank ?? '—' ?></td>
    <td style="color:#94a3b8;font-size:11px"><?= $r->created_at?->format('Y-m-d H:i:s') ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<!-- Fix assessment -->
<h2>🔧 Diagnosis</h2>
<div class="card">
<?php
    if ($hasDataMismatch) {
        echo notice(
            '<strong>Root cause confirmed:</strong> ' . $results->count() . ' result(s) exist but the sessions table has 0 rows for this exam. '
            . 'The controller fix (fallback to <code>OnlineExamResult</code>) will now display these students as <em>Submitted</em> and count them in Completed / All Attended. '
            . 'Reload the Live Monitor to verify.',
            true
        );
    } elseif ($sessions->isNotEmpty() && $results->isEmpty()) {
        echo notice('Sessions exist but no results yet — students may still be taking the exam, or scoring failed after submission. Check <code>ExamScoringService</code> logs.');
    } elseif ($sessions->isNotEmpty() && $results->isNotEmpty()) {
        $orphanResults = $results->filter(fn($r) => !$sessionStudentIds->contains($r->student_id));
        if ($orphanResults->isNotEmpty()) {
            echo notice($orphanResults->count() . ' result(s) have student IDs that do NOT match any session row. Possible orphaned data.');
        } else {
            echo notice('All results match session student IDs. Data is consistent. Live Monitor should display correctly.', false);
        }
    } else {
        echo notice('No sessions and no results. Students haven\'t taken this exam yet, or all data was cleared.');
    }
?>
</div>

<?php
    } catch (\Throwable $e) {
        echo "<div style='background:#2d0a0a;border:1px solid #991b1b;color:#fca5a5;padding:16px;border-radius:8px'>";
        echo "<strong>❌ Exception:</strong> " . htmlspecialchars($e->getMessage());
        echo "<pre style='margin-top:10px;font-size:11px;color:#94a3b8'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
    done:
endif;
?>

<p style="margin-top:32px;color:#334155;font-size:11px;text-align:center">
  ⚠ Debug tool — delete <code>public/debug-live-monitor.php</code> when done.
</p>
</body>
</html>
