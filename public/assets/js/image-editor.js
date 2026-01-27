var imageEditorModal;
var fabricCanvas;
var currentPhotoId;
var currentPhotoType;
var isDrawingMode = false;

document.addEventListener('DOMContentLoaded', function() {
    // Check if modal element exists
    var modalEl = document.getElementById('imageEditorModal');
    if (modalEl) {
        imageEditorModal = new bootstrap.Modal(modalEl, {
            keyboard: false,
            backdrop: 'static'
        });

        // Initialize Fabric
        // fabricCanvas = new fabric.Canvas('editor-canvas'); 
        // Initializing here might be too early if canvas is hidden in modal
    }
});

function openEditor(type, id, url, description, obs) {
    currentPhotoId = id;
    currentPhotoType = type;

    // Reset UI
    document.getElementById('editor-description').value = description || '';
    document.getElementById('editor-obs').value = obs || '';
    document.getElementById('editor-loading').style.display = 'flex';
    document.getElementById('editor-content').style.display = 'none';

    imageEditorModal.show();

    // Destroy previous instance if exists to avoid dupes/memory leaks
    if (fabricCanvas) {
        fabricCanvas.dispose();
    }
    
    // Create new canvas instance
    fabricCanvas = new fabric.Canvas('editor-canvas', {
        isDrawingMode: false
    });

    // Set brush props
    fabricCanvas.freeDrawingBrush = new fabric.PencilBrush(fabricCanvas);
    fabricCanvas.freeDrawingBrush.width = 5;
    fabricCanvas.freeDrawingBrush.color = "red";

    // Load Image
    fabric.Image.fromURL(url, function(img) {
        var maxWidth = 800; // Modal body width approx
        var maxHeight = 600;
        
        // Scale Logic
        var scale = 1;
        if (img.width > maxWidth || img.height > maxHeight) {
            var scaleX = maxWidth / img.width;
            var scaleY = maxHeight / img.height;
            scale = Math.min(scaleX, scaleY);
        }
        
        img.set({
            scaleX: scale,
            scaleY: scale,
            originX: 'left',
            originY: 'top'
        });

        // Resize canvas to fit image
        fabricCanvas.setWidth(img.width * scale);
        fabricCanvas.setHeight(img.height * scale);
        
        fabricCanvas.setBackgroundImage(img, fabricCanvas.renderAll.bind(fabricCanvas));
        
        document.getElementById('editor-loading').style.display = 'none';
        document.getElementById('editor-content').style.display = 'block';
    }, { crossOrigin: 'anonymous' }); // CORS safety
}

function toggleDrawing() {
    isDrawingMode = !isDrawingMode;
    fabricCanvas.isDrawingMode = isDrawingMode;
    var btn = document.getElementById('btn-draw');
    if (isDrawingMode) {
        btn.classList.add('btn-warning');
        btn.classList.remove('btn-outline-secondary');
        btn.innerHTML = '<i class="fa-solid fa-pencil"></i> Des. Desenhar';
    } else {
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-outline-secondary');
        btn.innerHTML = '<i class="fa-solid fa-pencil"></i> Desenhar';
    }
}

function clearCanvas() {
    // Clear annotations (objects), keep background image
    fabricCanvas.getObjects().forEach(function(o) {
        fabricCanvas.remove(o);
    });
}

function saveChanges() {
    var btn = document.getElementById('btn-save-all');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';

    // 1. Save Details
    var desc = document.getElementById('editor-description').value;
    var obs = document.getElementById('editor-obs').value;

    fetch(BASE_URL + '/photos/update-details', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: currentPhotoId,
            type: currentPhotoType,
            description: desc,
            observacoes: obs
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message);

        // 2. Save Image
        // Get data URL (without background if we want to layer, but here we want to burn it in? 
        // Actually, user probably wants to save the drawing ON TOP of the image permanently.)
        // fabricCanvas.toDataURL() exports everything.
        var dataURL = fabricCanvas.toDataURL({
            format: 'jpeg',
            quality: 0.8
        });

        return fetch(BASE_URL + '/photos/save-image', {
            method: 'POST',
             headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: currentPhotoId,
                type: currentPhotoType,
                image: dataURL
            })
        });
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message);
        
        alert('Mudanças salvas com sucesso!');
        imageEditorModal.hide();
        location.reload(); // Reload to show new image/details
    })
    .catch(err => {
        alert('Erro ao salvar: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Zoom
function zoomIn() {
    var zoom = fabricCanvas.getZoom();
    zoom *= 1.1;
    if (zoom > 5) zoom = 5;
    fabricCanvas.setZoom(zoom);
}

function zoomOut() {
    var zoom = fabricCanvas.getZoom();
    zoom /= 1.1;
    if (zoom < 0.1) zoom = 0.1;
    fabricCanvas.setZoom(zoom);
}
