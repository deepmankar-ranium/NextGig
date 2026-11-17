import { Home, Briefcase, ClipboardList, Users, Sparkles, MessageSquare, FolderKanban } from 'lucide-vue-next';

export const ROLE_EMPLOYER = 2;
export const ROLE_SEEKER = 3;

/** Main tabs (desktop top bar). Home is the logo. */
export const primaryNav = (roleId, badges = {}) => {
  if (roleId === ROLE_EMPLOYER) {
    return [
      { name: 'Jobs', href: '/jobs', icon: Briefcase },
      { name: 'My postings', href: '/posted-jobs', icon: FolderKanban },
      { name: 'Applicants', href: '/view-applications', icon: Users },
      { name: 'Assistant', href: '/chat', icon: Sparkles },
    ];
  }

  return [
    { name: 'Jobs', href: '/jobs', icon: Briefcase },
    { name: 'My applications', href: '/applied-jobs', icon: ClipboardList, badge: badges.applications },
    { name: 'Assistant', href: '/chat', icon: Sparkles },
  ];
};

/** Bottom tab bar on phones: 5 items max, Home first, Inbox included. */
export const mobileNav = (roleId, badges = {}) => {
  const home = { name: 'Home', href: '/home', icon: Home };
  const inbox = { name: 'Inbox', href: '/messages', icon: MessageSquare, badge: badges.inbox };

  if (roleId === ROLE_EMPLOYER) {
    return [home, { name: 'Postings', href: '/posted-jobs', icon: FolderKanban }, { name: 'Applicants', href: '/view-applications', icon: Users }, inbox, { name: 'Assistant', href: '/chat', icon: Sparkles }];
  }

  return [home, { name: 'Jobs', href: '/jobs', icon: Briefcase }, { name: 'Applied', href: '/applied-jobs', icon: ClipboardList, badge: badges.applications }, inbox, { name: 'Assistant', href: '/chat', icon: Sparkles }];
};

/** `/jobs` is active on `/jobs/12` too, but `/jobs` is not active on `/jobsearch`. */
export const isActive = (url, href) => {
  const path = url.split('?')[0];
  return path === href || path.startsWith(`${href}/`);
};

export const initials = (name = '') => {
  const words = name.trim().split(/\s+/).filter(Boolean);
  if (!words.length) return '?';
  return (words[0][0] + (words.length > 1 ? words[words.length - 1][0] : '')).toUpperCase();
};
