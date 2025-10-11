// ┌────────────────────────────────────────────┐
// │ Wait Tools — Promise-based polling factory │
// └────────────────────────────────────────────┘

export interface WaitOptions {
    intervalTime?: number; // ms between checks
    maxAttempts?: number;  // max retries before rejecting
    onTick?: (attempt: number) => void;         // called on each attempt
    onResolve?: (value: unknown) => void;       // called when resolved
    onCancel?: () => void;                      // called if cancelled
    onError?: (error: unknown) => void;         // called on thrown error
}

export type SyncResolvable<T> = () => T | null | undefined | false;
export type AsyncResolvable<T> = () => Promise<T | null | undefined | false>;

interface WaitFactoryConfig<T> {
    getValue: () => T | Promise<T | null | undefined | false>;
    isAsync?: boolean;
    cancelable?: boolean;
    options?: WaitOptions;
}

// Internal logic used by all wait functions
function internalWaiter<T>(config: WaitFactoryConfig<T>) {
    const {
        getValue,
        isAsync = false,
        cancelable = false,
        options = {}
    } = config;

    const {
        intervalTime = 500,
        maxAttempts = 20,
        onTick,
        onResolve,
        onCancel,
        onError
    } = options;

    let attempts = 0;
    let cancelled = false;

    const promise = new Promise<T>((resolve, reject) => {
        const tick = async () => {
            if (cancelled) {
                onCancel?.();
                reject(new Error('Wait cancelled manually.'));
                return;
            }

            try {
                const result = isAsync ? await getValue() : (getValue() as T | null | undefined | false);
                if (result) {
                    onResolve?.(result);
                    resolve(result as T);
                    return;
                }
            } catch (err) {
                onError?.(err);
                reject(err);
                return;
            }

            if (++attempts >= maxAttempts) {
                reject(new Error('Max attempts reached without condition being met.'));
            } else {
                onTick?.(attempts);
                setTimeout(tick, intervalTime);
            }
        };

        tick();
    });

    return cancelable
        ? { promise, cancel: () => (cancelled = true) }
        : promise;
}

/**
 * Creates a polling-based wait function with optional async and cancelable behavior.
 * Returns a callable function that accepts a resolver and options.
 */
export function createWaitFactory<T>(
    config: Omit<WaitFactoryConfig<T>, 'getValue'> & { isAsync?: boolean }
): (
    getValue: () => T | Promise<T | null | undefined | false>,
    options?: WaitOptions
) => Promise<T> | { promise: Promise<T>; cancel: () => void } {
    return (getValue, options) => {
        return internalWaiter({ ...config, getValue, options });
    };
}

/**
 * Waits until a synchronous function returns a truthy value, then resolves with that value.
 *
 * Useful for waiting on DOM elements, attributes, or any synchronous readiness logic.
 *
 * @example
 * waitUntilResolved(() => document.querySelector('#my-element'))
 *   .then((el) => el?.classList.add('visible'));
 *
 * @example
 * waitUntilResolved(() => {
 *   const category = document.querySelector('[data-type="category"] select');
 *   const service = document.querySelector('[data-type="service"] select');
 *   return category && service ? [category, service] : null;
 * }).then(([category, service]) => {
 *   validateSelects(category, service);
 * });
 */
export const waitUntilResolved = createWaitFactory({ isAsync: false });

/**
 * Waits until an async function returns a truthy value, then resolves with that value.
 *
 * Useful for waiting on external data, async APIs, or delayed readiness logic.
 *
 * @example
 * waitUntilResolvedAsync(async () => {
 *   const config = await fetchConfig();
 *   return config?.isReady ? config : null;
 * }).then((config) => initializeApp(config));
 *
 * @example
 * waitUntilResolvedAsync(async () => {
 *   await new Promise(r => setTimeout(r, 100));
 *   return document.querySelector('.dynamic-element');
 * }).then((el) => {
 *   el?.classList.add('visible');
 * });
 */
export const waitUntilResolvedAsync = createWaitFactory({ isAsync: true });

/**
 * Creates a wait handle that can be manually cancelled.
 *
 * Useful for waiting on conditions that may become irrelevant (e.g. user navigates away).
 *
 * @example
 * const handle = createWaitHandle(() => document.querySelector('.modal'));
 *
 * handle.promise
 *   .then((modal) => modal?.classList.add('visible'))
 *   .catch((err) => console.warn('Modal wait cancelled or failed:', err));
 *
 * document.querySelector('.close-button')?.addEventListener('click', () => {
 *   handle.cancel();
 * });
 */
export const createWaitHandle = createWaitFactory({ cancelable: true });
