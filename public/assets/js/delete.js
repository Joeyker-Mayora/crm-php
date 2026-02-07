document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            if (!id) return;

            this.setAttribute('disabled', 'true');
            try {
                const url = 'api/api.php?id=' + encodeURIComponent(id);
                const res = await fetch(url, { method: 'POST' });
                if (!res.ok) throw new Error('Error en la petición');
                location.reload();
            } catch (err) {
                alert('No se pudo eliminar el usuario. Intenta nuevamente.');
                this.removeAttribute('disabled');
            }
        });
    });
});
