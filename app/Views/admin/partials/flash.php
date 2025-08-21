<?php if(session()->getFlashdata('success')): ?>
  <div class="mb-4 rounded-md bg-green-50 p-4 text-green-800">
    <i class="fas fa-check-circle mr-2"></i><?= esc(session()->getFlashdata('success')) ?>
  </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
  <div class="mb-4 rounded-md bg-red-50 p-4 text-red-800">
    <i class="fas fa-exclamation-circle mr-2"></i><?= esc(session()->getFlashdata('error')) ?>
  </div>
<?php endif; ?>
