const sameDay = (a, b) => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();

export const timeLabel = (iso) => (iso ? new Date(iso).toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' }) : '');

export const dayLabel = (iso) => {
  const d = new Date(iso);
  const today = new Date();
  const yesterday = new Date(); yesterday.setDate(today.getDate() - 1);
  if (sameDay(d, today)) return 'Today';
  if (sameDay(d, yesterday)) return 'Yesterday';
  return d.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short', ...(d.getFullYear() !== today.getFullYear() ? { year: 'numeric' } : {}) });
};

/** Short list timestamp: "14:05", "Yesterday", "Mon", "12 Mar". */
export const listTime = (iso) => {
  if (!iso) return '';
  const d = new Date(iso);
  const now = new Date();
  if (sameDay(d, now)) return timeLabel(iso);
  const days = (now - d) / 86400000;
  if (days < 2) return 'Yesterday';
  if (days < 7) return d.toLocaleDateString(undefined, { weekday: 'short' });
  return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
};

const COLORS = ['bg-indigo-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-violet-500', 'bg-teal-500', 'bg-orange-500'];
export const avatarColor = (id) => COLORS[Math.abs(Number(id) || 0) % COLORS.length];

/**
 * Group a flat message list into day sections, and within a day into runs from the same
 * sender (sent within 5 minutes), so avatars and times aren't repeated on every bubble.
 */
export const groupMessages = (messages) => {
  const days = [];
  for (const m of messages) {
    const at = new Date(m.created_at);
    let day = days[days.length - 1];
    if (!day || !sameDay(new Date(day.date), at)) {
      day = { date: m.created_at, runs: [] };
      days.push(day);
    }
    const run = day.runs[day.runs.length - 1];
    const last = run?.messages[run.messages.length - 1];
    if (run && run.senderId === m.sender_id && at - new Date(last.created_at) < 5 * 60 * 1000) {
      run.messages.push(m);
    } else {
      day.runs.push({ senderId: m.sender_id, messages: [m] });
    }
  }
  return days;
};

const escapeHtml = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

/**
 * Minimal, safe formatting for assistant replies: paragraphs, **bold**, `code`, and
 * "-" / "1." lists. Text is HTML-escaped first, so this is safe to use with v-html.
 */
export const formatAssistant = (text = '') => {
  const inline = (s) => escapeHtml(s)
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/`([^`]+)`/g, '<code class="rounded bg-gray-100 px-1 py-0.5 text-[0.85em]">$1</code>');

  const out = [];
  let list = null;
  const closeList = () => { if (list) { out.push(`</${list}>`); list = null; } };

  for (const raw of text.replace(/\r/g, '').split('\n')) {
    const line = raw.trimEnd();
    const bullet = line.match(/^\s*[-*•]\s+(.*)$/);
    const numbered = line.match(/^\s*\d+[.)]\s+(.*)$/);

    if (bullet || numbered) {
      const type = bullet ? 'ul' : 'ol';
      if (list !== type) { closeList(); out.push(type === 'ul' ? '<ul class="my-2 list-disc space-y-1 pl-5">' : '<ol class="my-2 list-decimal space-y-1 pl-5">'); list = type; }
      out.push(`<li>${inline((bullet || numbered)[1])}</li>`);
    } else if (line.trim() === '') {
      closeList();
    } else {
      closeList();
      const heading = line.match(/^#{1,4}\s+(.*)$/);
      out.push(heading ? `<p class="mt-3 font-semibold">${inline(heading[1])}</p>` : `<p class="my-2">${inline(line)}</p>`);
    }
  }
  closeList();
  return out.join('');
};
