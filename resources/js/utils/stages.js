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
