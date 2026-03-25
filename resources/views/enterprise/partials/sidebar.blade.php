<aside class="mailbox-sidebar">
    <div class="mailbox-sidebar-header">
        <i class="fas fa-building me-2"></i>Enterprise
    </div>
    <nav class="nav flex-column">
        <a class="nav-link {{ ($activeSection ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('enterprise') }}"><i class="fas fa-th-large me-2"></i>Dashboard</a>
        <a class="nav-link {{ ($activeSection ?? '') === 'pending' ? 'active' : '' }}" href="{{ route('enterprise.mailbox', ['filter' => 'pending']) }}"><i class="fas fa-clock me-2"></i>Pending mail</a>
        <a class="nav-link {{ ($activeSection ?? '') === 'completed' ? 'active' : '' }}" href="{{ route('enterprise.mailbox', ['filter' => 'completed']) }}"><i class="fas fa-check-circle me-2"></i>Completed mail</a>
        <a class="nav-link {{ ($activeSection ?? '') === 'address-book' ? 'active' : '' }}" href="{{ route('enterprise.address-book') }}"><i class="fas fa-address-book me-2"></i>Address Book</a>
        <a class="nav-link {{ ($activeSection ?? '') === 'schedule-mail' ? 'active' : '' }}" href="{{ route('enterprise.schedule-mail') }}"><i class="fas fa-paper-plane me-2"></i>Schedule mail</a>
    </nav>
</aside>
