<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="{{route('index')}}" class="b-brand text-primary">
        <!-- ========   Change your logo from here   ============ -->
        <img src="../assets/images/logo-dark.svg" alt="" class="logo logo-lg" />
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label>Dashboard</label>
          <i class="ti ti-dashboard"></i>
        </li>
        <li class="pc-item">
          <a href="{{route('index')}}" class="pc-link"
            ><span class="pc-micon"><i class="ti ti-dashboard"></i></span><span class="pc-mtext">Default</span></a
          >
        </li>

        <li class="pc-item pc-caption">
          <label>Elements</label>
          <i class="ti ti-apps"></i>
        </li>
        <li class="pc-item">
          <a href="../elements/bc_typography.html" class="pc-link">
            <span class="pc-micon"><i class="ti ti-typography"></i></span>
            <span class="pc-mtext">Typography</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="{{route('admin.subscription-plans.index')}}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-typography"></i></span>
            <span class="pc-mtext">Subscription</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="{{ route('admin.users.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-users"></i></span>
            <span class="pc-mtext">Users</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="{{ route('admin.recharges.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-wallet"></i></span>
            <span class="pc-mtext">Recharge Requests</span>
          </a>
        </li>

      </ul>

      <div class="w-100 text-center">
        <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
      </div>
    </div>
  </div>
</nav>