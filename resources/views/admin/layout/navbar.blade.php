<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl flex-nowrap" id="navbarBlur" data-scroll="true" style="white-space: nowrap; overflow-x: auto;">
    <div class="container-fluid py-1 px-3 d-flex flex-row align-items-center flex-nowrap" style="white-space: nowrap;">
        <nav aria-label="breadcrumb" class="flex-shrink-0">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
            </ol>
        </nav>

        <ul class="navbar-nav d-flex align-items-center justify-content-end flex-row flex-nowrap mb-0" style="white-space: nowrap;overflow: hidden;">


             <li class="mt-3">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn w-100 bg-gradient-danger text-white fw-bold py-2" style="border-radius: 8px; transition: 0.3s;">
                                <i class="fa fa-sign-out"></i> Logout
                            </button>
                        </form>
                    </li>

            <li class="nav-item d-xl-none ps-3 d-flex align-items-center flex-shrink-0" style="margin-right: 5px;">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </a>
            </li>

            {{-- <li class="nav-item dropdown pe-3 d-flex align-items-center flex-shrink-0">
                <a href="javascript:;" class="nav-link text-body p-0 d-flex align-items-center" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="material-symbols-rounded fs-4 me-2">account_circle</i>
                    <span class="d-none d-md-inline fw-bold">Profile</span>
                    <i class="ms-1 fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-lg p-3"
                    aria-labelledby="dropdownMenuButton" style="min-width: 220px;">
                    <!-- Profile Section -->
                    <li class="dropdown-header text-center pb-2 border-bottom">
                        <i class="material-symbols-rounded fs-4 me-2">account_circle</i>
                        <p class="fw-bold text-dark mb-0">Hello, admin</p>
                    </li>
                    <!-- Logout Button -->
                    <li class="mt-3">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn w-100 bg-gradient-danger text-white fw-bold py-2" style="border-radius: 8px; transition: 0.3s;">
                                <i class="fa fa-sign-out"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </li> --}}
        </ul>
    </div>
</nav>
