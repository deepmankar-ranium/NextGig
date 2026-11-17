export const PIPELINE = ['applied', 'viewed', 'shortlisted', 'interview', 'offer'];

export const STAGE_LABELS = {
  applied: 'Applied',
  viewed: 'Viewed',
  shortlisted: 'Shortlisted',
  interview: 'Interview',
  offer: 'Offer',
  rejected: 'Not selected',
};

export const stageBadgeClass = (status) => ({
  applied: 'bg-gray-100 text-gray-700',
  viewed: 'bg-blue-100 text-blue-700',
  shortlisted: 'bg-indigo-100 text-indigo-700',
  interview: 'bg-amber-100 text-amber-800',
  offer: 'bg-green-100 text-green-700',
  rejected: 'bg-red-100 text-red-700',
}[status] || 'bg-gray-100 text-gray-700');

/** Stages an employer can move to without "reopen" (forward only, or reject). */
export const nextStages = (status) => {
  if (status === 'offer' || status === 'rejected') return [];
  const i = PIPELINE.indexOf(status);
  return [...PIPELINE.slice(i + 1).filter((s) => s !== 'viewed'), 'rejected'];
};

/**
 * "Ask the assistant" link for an application: the question depends on where it stands.
 */
export const assistantLinkFor = (jobId, title, status) => {
  const q = {
    applied: `How can I follow up on my application for ${title} without being pushy?`,
    viewed: `The employer viewed my application for ${title}. What can I do to stand out now?`,
    shortlisted: `I've been shortlisted for ${title}. How should I prepare for the next step?`,
    interview: `Help me prepare for my interview for ${title}. What questions should I expect?`,
    offer: `I got an offer for ${title}. What should I check before accepting, and how do I negotiate?`,
    rejected: `I wasn't selected for ${title}. What should I work on for similar roles?`,
  }[status] ?? `Help me with my application for ${title}.`;
  const label = {
    shortlisted: 'Prepare for the next step', interview: 'Prepare for the interview', offer: 'Review the offer',
    rejected: 'What to work on next',
  }[status] ?? 'Ask how to stand out';
  return { href: `/chat?job=${jobId}&q=${encodeURIComponent(q)}`, label };
};
