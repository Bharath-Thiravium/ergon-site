function loadAllCharts() {
    fetchOwnerPrefix().then(prefix => {
        console.log('loadAllCharts called with prefix:', prefix);
        if (!prefix) {
            console.warn('No prefix selected for charts');
            return;
        }
        if (window._dashboardCharts && window._dashboardCharts.renderAllCharts) {
            console.log('Calling renderAllCharts with prefix:', prefix);
            window._dashboardCharts.renderAllCharts(prefix);
        } else {
            console.error('_dashboardCharts not available');
        }
    });
}

function loadCashFlow() {
    fetchOwnerPrefix().then(prefix => {
        if (!prefix) return;
        
        fetch(`/ergon-site/src/api/dashboard/invoices.php?prefix=${encodeURIComponent(prefix)}`)
            .then(r => r.json())
            .then(d => {
                if (d.success && d.data) {
                    const outstanding = d.data.total_value - (d.data.paid || 0);
                    document.getElementById('expectedInflow').textContent = '₹' + outstanding.toLocaleString();
                }
            })
            .catch(e => console.warn('Cash flow load failed:', e));
    });
}

function fetchOwnerPrefix() {
    const hiddenPrefix = document.getElementById('companyPrefixHidden');
    const userRole = document.body.getAttribute('data-user-role');
    
    if (userRole === 'company_owner' || hiddenPrefix) {
        return Promise.resolve('BKGE');
    }
    
    return fetch('/ergon-site/src/api/owner-prefix.php')
        .then(r => r.json())
        .then(d => d.success ? d.prefix : 'BKGE')
        .catch(() => 'BKGE');
}

// Auto-trigger on page load
document.addEventListener('DOMContentLoaded', () => {
    const prefixInput = document.getElementById('companyPrefix');
    const userRole = document.body.getAttribute('data-user-role');
    const isCompanyOwner = userRole === 'company_owner';
    
    if (prefixInput) {
        if (isCompanyOwner) {
            prefixInput.value = 'BKGE';
            console.log('Company owner detected - using BKGE prefix');
        } else {
            prefixInput.disabled = true;
            prefixInput.placeholder = 'Loading from owner panel...';
            
            fetchOwnerPrefix().then(prefix => {
                prefixInput.value = prefix;
                prefixInput.placeholder = `Using: ${prefix}`;
            });
        }
    }
});
