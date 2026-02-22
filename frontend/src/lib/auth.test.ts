import { beforeEach, describe, expect, it } from 'vitest';
import { tokenStore } from './auth';

describe('tokenStore', () => {
  beforeEach(() => {
    tokenStore.clear();
  });

  it('returns null when no token exists', () => {
    expect(tokenStore.get()).toBeNull();
  });

  it('stores and retrieves token', () => {
    tokenStore.set('abc123');

    expect(tokenStore.get()).toBe('abc123');
  });

  it('clears token', () => {
    tokenStore.set('abc123');
    tokenStore.clear();

    expect(tokenStore.get()).toBeNull();
  });
});
