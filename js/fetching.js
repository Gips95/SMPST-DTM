export default async function fetchDataJson(url, formdata, method){
    try {
        const response = await fetch(url, {
            method:method,
            body: formdata,
            mode:'no-cors'
        })
        const data = await response.json()
        return data
    } catch (error) {
        return new Error(error.message)
    }
}

//funcion a implementar