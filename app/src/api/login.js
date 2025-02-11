export async function login(email, password) {
    const response = await fetch('http://localhost:8680/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
    })

    const data = await response.json()

    if (!response.ok) {
        throw { response: { status: response.status, data } }
    }

    return data
}