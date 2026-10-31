<aside class="sidebar">
    <div class="school-logo">
        <img src="<?php echo BASE_URL ?>/assets/images/bcp-logo.png" alt="School Logo">


       <div class="sidebar-icons d-flex align-items-center gap-3">
    
                <button type="button" id="notif-btn" class="btn position-relative p-0 border-0 bg-transparent">
                    <i class="fa-regular fa-bell fs-5"></i>
                    <span id="notifCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        
                    </span>
                </button>

        <div class="dropdown">
               <button
                type="button"
                id="user-btn"
                class="btn p-0 border-0 bg-transparent"
                data-bs-toggle="dropdown"
                aria-expanded="false"
               >
               <i class="fa-regular fa-circle-user fs-5"></i>
             </button>

        <ul class="dropdown-menu dropdown-menu-end">
           <li>
             <a class="dropdown-item" href="#">
                Profile
            </a>
           </li>

           <li>
            <a class="dropdown-item" href="#">
                Settings
            </a>
           </li>

           <li><hr class="dropdown-divider"></li>

           <li>
            
             <a class="dropdown-item text-danger" href="sms/auth/logout.php" >
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </a>
          </li>
         </ul>
        </div>

         </div>
       

        </div>


    </div>
    <div class="sidebar-header">
        <div class="user-avatar"><?= $first_two ?></div>
        <h1><?= $name ?></h1>
        <p class="user-id"><?= $studentSchoolInfo['student_number'] ?></p>
    </div>


    <div class="accordion" id="sidebarMenu">

        <!-- dashboard -->

        <h2>
            DASHBOARD
        </h2>

           <hr>

        <ul>
            <li><a href="<?php echo BASE_URL ?>/home" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">Dashboard</a></li>
        </ul>
         <ul>
            <li><a href="<?php echo BASE_URL ?>/grades" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">Semestal Grades</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/enrollment" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">Enrollment</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/self-enrollment" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">Self-Enrollment</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/events" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">Events</a></li>
        </ul>

        <div class="separator"></div>
        <h2>Scholarship</h2>
           <hr>

        <ul>
            <li><a href="<?php echo BASE_URL ?>/scholarship" class="menu-link  d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">
                Scholarship</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/scholarship-offered" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">
                Scholarship Offerred</a></li>
        </ul>
        <ul>
            <li><a href="<?php echo BASE_URL ?>/my-applications" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">
                My Applications</a></li>
        </ul>
 
        <!-- settings  -->
     
        <div class="separator"></div>
        <h2>SETTINGS</h2>

           <hr>


         <ul>
            <li>
                <a href="<?php echo BASE_URL ?>/my-applications" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="dashboard" style="margin-left: 8px;">
                Profile
            </a></li>
        </ul>
       
     
        <div class="separator"></div>

           <hr>


         <!-- request -->
        <h2>
            <a href="#docuRequest" class="d-flex justify-content-between align-items-center text-decoration-none text-white">
                Request Documents
            </a>
        </h2>

        <hr>

        <ul>
           <li><a href="<?php echo BASE_URL ?>/document-request" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="request" style="margin-left: 8px;">Document Request</a></li>
        </ul>

        <ul>
         <li><a href="<?php echo BASE_URL ?>/document-status" class="menu-link d-flex justify-content-between align-items-center text-decoration-none text-white" id="status" style="margin-left: 8px;">Document Status</a></li>
        </ul>
  

        <div class="separator"></div>

        <hr>
         <div class="mt-3">
            <a href="<?= BASE_URL ?>/logout"
                class="d-flex justify-content-between align-items-center text-decoration-none text-white"
                style="margin-left: 8px;">
                <span>Logout</span>
            </a>
        </div>


</aside>