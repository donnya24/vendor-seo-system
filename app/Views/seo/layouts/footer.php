
<script>
document.addEventListener('alpine:init', () => {
  Alpine.store('ui', { 
    sidebar: window.innerWidth > 768, 
    modal: null 
  });

  Alpine.store('app', {
    stats: <?= isset($stats) ? json_encode($stats) : '{}' ?>,
    recentLeads: <?= isset($recentLeads) ? json_encode($recentLeads) : '[]' ?>,
    topKeywords: <?= isset($topKeywords) ? json_encode($topKeywords) : '[]' ?>,
    unread: <?= isset($stats['unread']) ? (int)$stats['unread'] : 0 ?>,
    init() { 
      console.log('Vendor Dashboard initialized');
    }
  });
});
</script>
</body>
</html>