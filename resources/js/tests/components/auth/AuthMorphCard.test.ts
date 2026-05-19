import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import AuthMorphCard from '@/components/auth/AuthMorphCard.vue';

const visit = vi.hoisted(() => vi.fn());

vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit,
  },
}));

describe('AuthMorphCard', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal('fetch', vi.fn());
    visit.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
  });

  it('posts auth forms as json and visits after the success animation', async () => {
    const fetchMock = vi.mocked(fetch);

    fetchMock.mockResolvedValue({
      ok: true,
      status: 204,
    } as Response);

    const wrapper = mount(AuthMorphCard, {
      props: {
        action: '/login',
        successHref: '/ideas',
        title: 'Login',
      },
      slots: {
        default: '<input name="username" value="river"><button type="submit">Login</button>',
      },
    });

    await wrapper.get('form').trigger('submit');
    await flushPromises();

    expect(fetchMock).toHaveBeenCalledWith('/login', expect.objectContaining({
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    }));
    expect(wrapper.find('form').exists()).toBe(false);
    expect(wrapper.find('.auth-success-stage').exists()).toBe(true);

    await vi.advanceTimersByTimeAsync(2700);

    expect(visit).toHaveBeenCalledWith('/ideas');
  });

  it('surfaces async validation errors without navigating', async () => {
    const fetchMock = vi.mocked(fetch);

    fetchMock.mockResolvedValue({
      json: async () => ({
        errors: {
          username: ['These credentials do not match our records.'],
        },
      }),
      ok: false,
      status: 422,
    } as Response);

    const wrapper = mount(AuthMorphCard, {
      props: {
        action: '/login',
        successHref: '/ideas',
        title: 'Login',
      },
      slots: {
        default: `
          <template #default="{ errorsFor }">
            <input name="username">
            <p id="username-error">{{ errorsFor('username')[0] }}</p>
            <button type="submit">Login</button>
          </template>
        `,
      },
    });

    await wrapper.get('form').trigger('submit');
    await flushPromises();

    expect(wrapper.get('#username-error').text()).toBe('These credentials do not match our records.');
    expect(visit).not.toHaveBeenCalled();

    await wrapper.get('input[name="username"]').setValue('updated');

    expect(wrapper.get('#username-error').text()).toBe('');
  });
});
