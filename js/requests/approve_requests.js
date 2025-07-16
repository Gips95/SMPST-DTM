import fetchDataJson from '../fetching.js'
async function ModifyApprove(e){
    if(e.target.name == 'action'){
        try {
            const formdata = new FormData()
            const req_id = e.target.dataset.requestId
            formdata.append('request_id', req_id)
            formdata.append('action', e.target.value)

            const res = await fetchDataJson('endpoints/update_approval_request.php', formdata, 'POST')
            if(res.status == 400) throw new Error(res.msg)
            
            // Recargar la página después de éxito
            location.reload();
            
        } catch (error) {
            console.log(error.message)
        }
    }
}

document.addEventListener('click', ModifyApprove)