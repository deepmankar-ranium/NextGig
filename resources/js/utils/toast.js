import { useToast } from 'vue-toastification';
import AppToast from '@/Components/UI/AppToast.vue';

export const TOAST_OPTIONS = {
  position: 'top-right',
  timeout: 4500,
  maxToasts: 4,
  newestOnTop: true,
  hideProgressBar: true,
  closeButton: false,
  icon: false,
  draggable: true,
  pauseOnHover: true,
  pauseOnFocusLoss: true,
  closeOnClick: false, // AppToast decides (links navigate, others need the X)
  toastClassName: 'nextgig-toast',
  // Don't stack two toasts with the same text (e.g. a double submit).
  filterBeforeCreate: (toast, toasts) => (toasts.some((t) => t.id === toast.id) ? false : toast),
};

/**
 * notify({ variant: 'success' | 'error' | 'info' | 'message', title, body, href, avatar, id, timeout })
 */
export const notify = ({ timeout, id, ...props }) => {
  const toast = useToast();
  const mobile = typeof window !== 'undefined' && window.innerWidth < 640;

  return toast({ component: AppToast, props }, {
    ...(id ? { id } : {}),
    ...(timeout ? { timeout } : {}),
    ...(props.variant === 'error' ? { timeout: timeout ?? 7000 } : {}),
    position: mobile ? 'top-center' : 'top-right',
  });
};
