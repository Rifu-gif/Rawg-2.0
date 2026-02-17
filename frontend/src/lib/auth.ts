import { useSyncExternalStore } from 'react';

const TOKEN_KEY = 'gamehub_token';
const TOKEN_CHANGED_EVENT = 'gamehub_token_changed';

function emitTokenChange() {
  if (typeof window === 'undefined') return;
  window.dispatchEvent(new Event(TOKEN_CHANGED_EVENT));
}

export const tokenStore = {
  get(): string | null {
    if (typeof window === 'undefined') return null;
    return window.localStorage.getItem(TOKEN_KEY);
  },
  set(token: string) {
    if (typeof window === 'undefined') return;
    window.localStorage.setItem(TOKEN_KEY, token);
    emitTokenChange();
  },
  clear() {
    if (typeof window === 'undefined') return;
    window.localStorage.removeItem(TOKEN_KEY);
    emitTokenChange();
  },
};

function subscribe(callback: () => void) {
  if (typeof window === 'undefined') return () => {};

  window.addEventListener('storage', callback);
  window.addEventListener(TOKEN_CHANGED_EVENT, callback);

  return () => {
    window.removeEventListener('storage', callback);
    window.removeEventListener(TOKEN_CHANGED_EVENT, callback);
  };
}

export function useAuthToken() {
  return useSyncExternalStore(subscribe, tokenStore.get, () => null);
}
