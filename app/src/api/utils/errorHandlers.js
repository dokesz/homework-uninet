import { ref } from 'vue'

const forbidden = ref(false)

export function handleError(error, router) {
    if (error.response && error.response.status === 401) {
        router.push('/')
    } else {
        console.error('Error:', error)
    }
}

export { forbidden }