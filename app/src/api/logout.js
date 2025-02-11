export async function logout() {
    const response = await fetch('http://localhost:8680/auth/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem('authToken'),
        },
        body: JSON.stringify({}),
    });
    if (response.ok) {
        localStorage.removeItem('authToken');
        console.log('Logged out successfully');
    }
}