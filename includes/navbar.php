<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="/">VNN922-EASYEV</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMenu">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navMenu">
    <ul class="navbar-nav mr-auto">
      <?php if(isset($_SESSION['user_id'])): ?>
      <?php if($_SESSION['user_type']=='Administrator'): ?>
        <li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin Dashboard</a></li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="/user/index.php">My Charging</a></li>
      <?php endif; ?>
      <li class="nav-item"><a class="nav-link" href="/auth/logout.php">Logout</a></li>
      <?php else: ?>
      <li class="nav-item"><a class="nav-link" href="/auth/login.php">Login</a></li>
      <li class="nav-item"><a class="nav-link" href="/auth/register.php">Register</a></li>
      <?php endif; ?>
    </ul>
    <form class="form-inline" action="/search.php" method="get">
      <input class="form-control mr-sm-2" type="search" name="q" placeholder="Search...">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>
