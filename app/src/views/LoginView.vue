<template>
  <div id="app">
    <div class="greeting-wrapper">
      <div class="welcome-section">
        <h1 class="welcome-title">Üdvözöljük!</h1>
        <p class="welcome-text">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam eu fermentum nisi.
        </p>
      </div>
    </div>
    <div class="login-wrapper">
      <div class="login-section">
        <h2 class="login-title">uniadmin</h2>
        <form @submit.prevent="handleSubmit">
          <div class="input-group">
            <UserRound class="input-icon" />
            <input type="text" class="input-field" placeholder="Email" v-model="email" />
          </div>
          <div class="input-group">
            <KeyRound class="input-icon" />
            <input type="password" class="input-field" placeholder="Jelszó" v-model="password" />
          </div>
          <div class="forgot-password">
            <a href="#">Elfelejtett jelszó?</a>
          </div>
          <button type="submit" class="login-button">Bejelentkezés</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { KeyRound, UserRound } from 'lucide-vue-next'
import { login } from '../api/login'

const email = ref('')
const password = ref('')
const router = useRouter()

const handleSubmit = async () => {
  const res = await login(email.value, password.value)
  if (res.success && res.token) {
    localStorage.setItem('authToken', res.token)
    router.push('/dashboard')
  }
}
</script>

<style scoped>
#app {
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  width: 100%;
  height: 100vh;
}

.greeting-wrapper {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: linear-gradient(145deg, #d81ced, #042661);
}

.login-wrapper {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: linear-gradient(90deg, #a686e4, #b9ccec);
}

.welcome-section {
  color: white;
  padding: 2rem;
}

.welcome-title {
  font-size: 3.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  font-family: 'Segoe Script', cursive;
}

.welcome-text {
  font-size: 1.2rem;
  opacity: 0.9;
}

.login-title {
  color: #6b2b89;
  font-size: 3.5rem;
  margin-bottom: 2rem;
  font-weight: bold;
  text-align: center;
  font-family: 'Segoe Script', cursive;
}

.input-group {
  margin-bottom: 1.5rem;
  position: relative;
}

.input-field {
  width: 100%;
  padding: 0.8rem 1rem 0.8rem 3rem;
  border: none;
  border-radius: 2rem;
  background: white;
  font-size: 1rem;
}

.forgot-password {
  text-align: center;
  margin-bottom: 1.5rem;
}

.forgot-password a {
  color: #6b2b89;
  text-decoration: none;
  font-size: 0.9rem;
}

.login-button {
  width: 60%;
  display: block;
  margin: auto;
  padding: 0.8rem;
  border: none;
  border-radius: 2rem;
  background: #4a2b89;
  color: white;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.login-button:hover {
  background: #6b2b89;
}

.input-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b2b89;
}

@media (max-width: 768px) {
  #app {
    grid-template-columns: 1fr;
    padding: 1rem;
  }

  .welcome-section {
    text-align: center;
  }
}
</style>
