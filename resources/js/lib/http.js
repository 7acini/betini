const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

export async function apiFetch(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            ...(options.headers ?? {}),
        },
    });

    return response;
}

export function jsonBody(payload) {
    return JSON.stringify(payload);
}
