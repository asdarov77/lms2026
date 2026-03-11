import httpClient from './httpClient'
//console.log(httpClient, "httpClient")
const ENDPOINT = '/api/login'
//console.log(ENDPOINT, "ENDPOINT")
const login = (formData) => httpClient.post(ENDPOINT, formData)
//console.log(login, 'login auth.api.js')

export { login }
