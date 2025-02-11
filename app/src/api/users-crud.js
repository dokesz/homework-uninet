export async function listUsers() {
    const response = await fetch('http://localhost:8680/user/list', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`
        },
    });

    const data = await response.json();

    if (!response.ok) {
        throw { response: { status: response.status, data } };
    }

    return data;
}

export async function updateUser(user) {
    const response = await fetch(`http://localhost:8680/user/update/${user.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`
        },
        body: JSON.stringify(user),
    });

    const data = await response.json();

    if (!response.ok) {
        throw { response: { status: response.status, data } };
    }

    return data;
}

export async function deleteUser(user) {
    const response = await fetch(`http://localhost:8680/user/delete/${user.id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`
        },
    });

    const data = await response.json();

    if (!response.ok) {
        throw { response: { status: response.status, data } };
    }

    return data;
}