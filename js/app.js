function openEditModal(id, title, url) {
    document.getElementById('edit_link_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_url').value = url;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const sortableList = document.getElementById('sortable-links');

    if (sortableList) {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
        script.onload = function() {
            Sortable.create(sortableList, {
                animation: 150,
                handle: '.link-handle',
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    const items = sortableList.querySelectorAll('.link-item');
                    const order = Array.from(items).map(item => item.dataset.id).join(',');

                    fetch('/linkme/ajax_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=reorder_links&order=' + encodeURIComponent(order)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Erro ao reordenar links:', data.message);
                            alert('Erro ao salvar a nova ordem. Tente novamente.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao salvar a nova ordem. Tente novamente.');
                    });
                }
            });
        };
        document.head.appendChild(script);
    }
});
