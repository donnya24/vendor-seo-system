</div><!-- /.flex -->
<script>
  function adminDashboard(){
    return {
      sidebarOpen: window.innerWidth > 768,
      profileDropdownOpen:false,
      modalOpen:null,
      init(){
        const pref = localStorage.getItem('sidebarOpen');
        this.sidebarOpen = pref !== null ? (pref === 'true') : (window.innerWidth > 768);
        window.addEventListener('resize',()=>{ if(window.innerWidth <= 768) this.sidebarOpen=false; });
        this.$watch('sidebarOpen', v => localStorage.setItem('sidebarOpen', v));
      },
      openLogoutModal(){ this.modalOpen='logout' }
    }
  }
</script>
<script src="<?= base_url('js/app.js'); ?>"></script>
<?php if (!empty($jsFiles)) : foreach($jsFiles as $file): ?>
  <script src="<?= base_url($file); ?>"></script>
<?php endforeach; endif; ?>
</body></html>

</body>
</html>
