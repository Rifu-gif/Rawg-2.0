import { beforeEach, describe, expect, it } from 'vitest';
import { api } from './api';
import { tokenStore } from './auth';

describe('api client auth interceptor', () => {
  beforeEach(() => {
    tokenStore.clear();
  });

  it('adds bearer token when token exists', async () => {
    tokenStore.set('my-token');

    let authHeader: string | undefined;
    api.defaults.adapter = async (config) => {
      authHeader = config.headers?.Authorization as string | undefined;
      return {
        data: {},
        status: 200,
        statusText: 'OK',
        headers: {},
        config,
      };
    };

    await api.get('/games');

    expect(authHeader).toBe('Bearer my-token');
  });

  it('does not add authorization header when token is missing', async () => {
    let authHeader: string | undefined;
    api.defaults.adapter = async (config) => {
      authHeader = config.headers?.Authorization as string | undefined;
      return {
        data: {},
        status: 200,
        statusText: 'OK',
        headers: {},
        config,
      };
    };

    await api.get('/games');

    expect(authHeader).toBeUndefined();
  });
});
