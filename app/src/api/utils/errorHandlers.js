import { useRouter } from 'vue-router'
import { ref } from 'vue'

const forbidden = ref(false)
const router = useRouter()

export function handleError(error) {
    if (error.response && error.response.status === 401) {
        router.push('/')
    } else if (error.response && error.response.status === 403) {
        forbidden.value = true
    } else {
        console.error('Error:', error)
    }
}

export { forbidden }