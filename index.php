<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>ImageKit Uploader - Demo</title>
<style>
body{font-family:Inter,system-ui,Arial;padding:20px;background:#f5fbff}
.container{max-width:720px;margin:12px auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
#drop{border:2px dashed #3aa0ff;padding:28px;border-radius:10px;text-align:center;cursor:pointer;background:#f7fbff}
#drop.hover{background:#eaf6ff}
.preview{margin-top:18px}
.card{display:flex;gap:12px;align-items:center;background:#f2f9ff;padding:12px;border-radius:10px;margin-bottom:12px}
.card img{width:120px;height:auto;border-radius:8px;object-fit:cover}
.card .meta{flex:1}
.card input{width:100%;padding:8px;border-radius:6px;border:1px solid #d0e9ff}
button{background:#0b79ff;color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer}
small.error{color:#b00020}
</style>
</head>
<body>
<div class="container">
  <h1>ImageKit Image → URL</h1>
  <p>Drag & Drop images or click to choose. Files are uploaded to ImageKit and you get a permanent URL.</p>

  <div id="drop">Drop images here or click to select</div>
  <input id="fileInput" type="file" accept="image/*" multiple style="display:none" />

  <div id="preview" class="preview"></div>
  <div id="status"></div>
</div>

<script>
const drop = document.getElementById('drop');
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('preview');
const status = document.getElementById('status');

drop.addEventListener('click', ()=> fileInput.click());
fileInput.addEventListener('change', ()=> handleFiles(fileInput.files));

drop.addEventListener('dragover', e=> { e.preventDefault(); drop.classList.add('hover'); });
drop.addEventListener('dragleave', ()=> drop.classList.remove('hover'));
drop.addEventListener('drop', e=> { e.preventDefault(); drop.classList.remove('hover'); handleFiles(e.dataTransfer.files); });

function handleFiles(files){
  status.textContent = '';
  for (const file of files) {
    if (!file.type.startsWith('image/')) {
      status.innerHTML = '<small class="error">Only image files allowed.</small>';
      continue;
    }
    upload(file);
  }
}

async function upload(file){
  // create UI card
  const card = document.createElement('div'); card.className='card';
  const img = document.createElement('img'); img.src = URL.createObjectURL(file);
  const meta = document.createElement('div'); meta.className='meta';
  const input = document.createElement('input'); input.value = 'Uploading...'; input.readOnly = true;
  const btn = document.createElement('button'); btn.textContent='Copy URL'; btn.disabled=true;
  meta.appendChild(input); meta.appendChild(btn);
  card.appendChild(img); card.appendChild(meta);
  preview.prepend(card);

  const form = new FormData();
  form.append('image', file);

  try {
    const res = await fetch('upload.php', { method: 'POST', body: form });
    // Expect JSON
    const data = await res.json();
    if (data.success) {
      input.value = data.url;
      btn.disabled = false;
      btn.onclick = ()=> {
        navigator.clipboard.writeText(data.url);
        btn.textContent = 'Copied!';
        setTimeout(()=> btn.textContent='Copy URL', 1400);
      };
      // optionally show transformed thumbnail
      const thumb = document.createElement('img');
      thumb.src = data.url + '?tr=w-400'; // ImageKit transformation (resize)
      thumb.style.maxWidth = '100%';
      meta.appendChild(thumb);
    } else {
      input.value = 'Upload error: ' + (data.error || 'Unknown');
      console.error('Upload failed raw:', data.raw || data);
    }
  } catch (err) {
    input.value = 'Upload error: ' + err.message;
    console.error('Fetch error:', err);
  }
}
</script>
</body>
</html>