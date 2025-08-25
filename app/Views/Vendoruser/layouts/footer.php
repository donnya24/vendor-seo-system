<!-- Alpine Stores (inject data dari server) -->
<script>
document.addEventListener('alpine:init', () => {
  Alpine.store('ui', { 
    sidebar: window.innerWidth > 768, 
    modal: null 
  });

  Alpine.store('app', {
    stats: <?= $JSON($stats) ?>,
    recentLeads: <?= $JSON($recentLeads) ?>,
    topKeywords: <?= $JSON($topKeywords) ?>,
    unread: <?= (int)($stats['unread'] ?? 0) ?>,
    init() { 
      // Inisialisasi data jika diperlukan
      console.log('Vendor Dashboard initialized');
    }
  });
});
</script>
</body>
</html>