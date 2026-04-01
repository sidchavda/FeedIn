
@extends('layouts.master')

@section('styles')

@endsection

@section('content')
<br><br>
<div class="main-content app-content p-0">
   <br><br>
    <div class="container-fluid">
        <br><br>
        <!-- Start::row-1 -->
        <div class="row content-top">
            <div class="col-xxl-6 col-xl-12">
                <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body p-0">
                            <div class="p-4">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane show active fade p-0 border-0" id="activity-tab-pane"
                                    role="tabpanel" aria-labelledby="activity-tab-pane" tabindex="0">
                                    <h6 class="fw-semibold">Basic Information</h6>
                                    <div class="table-responsive mb-3 text-muted">
                                        <table class="table text-nowrap table-borderless">
                                        <tbody>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Father name
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->father_name}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Date of Birth
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->dob ? date('d-m-Y', strtotime($lead->dob)) : ''}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Gender
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->gender}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Emergency Contact
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->emergency_contact}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Blood Group
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->blood_group}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Disability
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->disability}}</td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <h6 class="fw-semibold">Contact Information</h6>
                                    <div class="table-responsive mb-3 text-muted">
                                        <table class="table text-nowrap table-borderless">
                                        <tbody>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Village
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->village}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    Police Station
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->police_station}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    State   
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->state}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted">
                                                    <div class="fw-semibold">
                                                    District
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->district}}</td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <h6 class="fw-semibold">Education Information</h6>
                                    <div class="table-responsive mb-3 text-muted">
                                        <table class="table text-nowrap table-borderless">
                                        <tbody>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Qualification
                                                    </div>
                                                </td>
                                                <td class="text-muted">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->qualification}}</td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <h6 class="fw-semibold">Work Experience</h6>
                                    <div class="table-responsive mb-3 text-muted">
                                        <table class="table text-nowrap table-borderless">
                                        <tbody>
                                            <tr class="product-list">
                                                <td class="text-muted wd-260">
                                                    <div class="fw-semibold">
                                                    Job Status  
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->job_status}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Current Company 
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->current_company}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Current Site Location
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->current_site_location}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Current Job Role
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->current_job_role}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Current Start Date
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->current_start_date}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Current End Date
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->current_end_date}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Current Skill Type   
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->current_skill_type}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Previous Company
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->previous_company}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Previous Site Location
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->previous_site_location}}</td>   
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Previous Job Role
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->previous_job_role}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Previous Start Date
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->previous_start_date}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Previous End Date
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->previous_end_date}}</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="text-muted  wd-260">
                                                    <div class="fw-semibold">
                                                    Previous Skill Type
                                                    </div>
                                                </td>
                                                <td class="text-muted ">
                                                    :
                                                </td>
                                                <td class="text-muted">{{$lead->previous_skill_type}}</td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <h6 class="fw-semibold">Other Information</h6>
                                    <div class="table-responsive mb-3 text-muted">
                                        <table class="table text-nowrap table-borderless">
                                            <tbody>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Pf  
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->pf}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Esi 
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->esi}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Bo Card  
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->bo_card}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Ayush Card
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->ayush_card}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Addhar Number
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->addhar_number}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Pan Number 
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->pan_number}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Pan Number 
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->pan_number}}</td>
                                                </tr>
                                                <tr class="product-list">
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        loan 
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->loan}}</td>
                                                </tr>
                                                <tr class="product-list">   
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Loan Amount
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->amount}}</td>
                                                </tr>
                                                <tr class="product-list">   
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Institution
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->institution}}</td>
                                                </tr>  <tr class="product-list">   
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        tenure
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->tenure}}</td>
                                                </tr>  <tr class="product-list">   
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Bank Name
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->bank_name}}</td>
                                                </tr>  <tr class="product-list">   
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        branch 
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td>
                                                    <td class="text-muted">{{$lead->branch}}</td>
                                                </tr>  <tr class="product-list">   
                                                    <td class="text-muted wd-260">
                                                        <div class="fw-semibold">
                                                        Account Number
                                                        </div>
                                                    </td>
                                                    <td class="text-muted ">
                                                        :
                                                    </td> 
                                                    <td class="text-muted">{{$lead->account_number}}</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade p-0 border-0" id="followers-tab-pane"
                                    role="tabpanel" aria-labelledby="followers-tab-pane" tabindex="0">
                                    <div class="card custom-card">
                                        <div class="card-body">
                                        <div class="d-sm-flex align-items-center lh-1">
                                            <div class="me-3">
                                                <span class="avatar avatar-md avatar-rounded">
                                                <img src="https://laravelui.spruko.com/synto/build/assets/img/users/1.jpg" alt="">
                                                </span>
                                            </div>
                                            <div class="flex-fill me-sm-2 mt-1 mt-sm-0">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control form-control-light" placeholder="Recipient's username" aria-label="Recipient's username with two button addons">
                                                    <button class="btn btn-light btn-wave d-none d-sm-block waves-effect waves-light" type="button"><i class="ri ri-emotion-line"></i></button>
                                                    <button class="btn btn-light btn-wave waves-effect waves-light" type="button"><i class="ri ri-pencil-line"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="card-body border-top">
                                        <ul class="d-sm-flex list-unstyled mb-0">
                                            <li class="me-3">
                                                <a class="d-flex align-items-center" href="javascript:void(0);">
                                                <i class="ri ri-image-2-line fs-20 me-1 text-primary"></i>
                                                <span class="text-muted fs-14">
                                                Image
                                                </span>
                                                </a>
                                            </li>
                                            <li class="me-3">
                                                <a class="d-flex align-items-center" href="javascript:void(0);">
                                                <i class="ri ri-vidicon-line fs-20 me-1 text-secondary"></i>
                                                <span class="text-muted fs-14">
                                                Video
                                                </span>
                                                </a>
                                            </li>
                                            <li class="me-3">
                                                <a class="d-flex align-items-center" href="javascript:void(0);">
                                                <i class="ri ri-attachment-2 fs-20 me-1 text-warning"></i>
                                                <span class="text-muted fs-14">
                                                Attachment
                                                </span>
                                                </a>
                                            </li>
                                            <li class="hidden md:flex me-3">
                                                <a class="d-flex align-items-center" href="javascript:void(0);">
                                                <i class="ri ri-hashtag fs-20 me-1  text-danger"></i>
                                                <span class="text-muted fs-14">
                                                Hashtag
                                                </span>
                                                </a>
                                            </li>
                                            <li class="hidden xxxl:flex me-3">
                                                <a class="d-flex align-items-center" href="javascript:void(0);">
                                                <i class="ri ri-at-line fs-20 me-1  text-info"></i>
                                                <span class="text-muted fs-14">
                                                Mention
                                                </span>
                                                </a>
                                            </li>
                                        </ul>
                                        </div>
                                        <div class="card-footer Profile-post-footer">
                                        <div class="d-flex justify-content-end">
                                            <select class="form-control" data-trigger name="product-category-add" id="product-category-add">
                                                <option value="">Public</option>
                                                <option value="Clothing">Friends Of Friends</option>
                                                <option value="Footwear">Only Me</option>
                                                <option value="Accesories">Public</option>
                                                <option value="Grooming">Private</option>
                                            </select>
                                            <button class="btn btn-primary ms-2" type="button">post</button>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="card custom-card">
                                        <div class="">
                                        <div class="p-3 d-sm-flex align-items-top">
                                            <div class="me-3 wd-200">
                                                <span class="">
                                                <img src="https://laravelui.spruko.com/synto/build/assets/img/gallery/9.jpg" alt="" class="w-100 rounded-1 border">
                                                </span>
                                            </div>
                                            <div class="flex-fill ">
                                                <p class="mb-1 fw-semibold lh-1 fs-14 mt-2 mt-sm-0">Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                                                <div class="mt-3 d-flex align-items-center justify-content-between mb-md-0 mb-2">
                                                    <div>
                                                    <div class="btn-list">
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-heart-line me-2"></i>30
                                                        </button>
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-thumb-up-line me-2"></i>25k
                                                        </button>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between">
                                                    <div class="mt-3 d-flex align-items-top">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/1.jpg" alt="img">
                                                    </span>
                                                    <div class="ms-2 fw-semibold flex-fill">
                                                        <p class="mb-0 lh-1">{{\Auth::user()->name}}</p>
                                                        <span class="fs-11 text-muted op-7"></span>
                                                    </div>
                                                    </div>
                                                    <div class="avatar-list-stacked mt-2 mt-sm-0">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/5.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                    +2
                                                    </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="card custom-card">
                                        <div class="">
                                        <div class="p-3 d-sm-flex align-items-top">
                                            <div class="me-3 wd-200">
                                                <span class="">
                                                <img src="https://laravelui.spruko.com/synto/build/assets/img/gallery/3.jpg" alt="" class="w-100 rounded-1 border">
                                                </span>
                                            </div>
                                            <div class="flex-fill ">
                                                <p class="mb-1 fw-semibold lh-1 fs-14 mt-2 mt-sm-0">Deserunt dolore ad incididunt excepteur excepteur Lorem</p>
                                                <div class="mt-3 d-flex align-items-center justify-content-between mb-md-0 mb-2">
                                                    <div>
                                                    <div class="btn-list">
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-heart-line me-2"></i>30
                                                        </button>
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-thumb-up-line me-2"></i>25k
                                                        </button>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between">
                                                    <div class="mt-3 d-flex align-items-top">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                    </span>
                                                    <div class="ms-2 fw-semibold flex-fill">
                                                        <p class="mb-0 lh-1">Sujika</p>
                                                        <span class="fs-11 text-muted op-7">5 hrs ago</span>
                                                    </div>
                                                    </div>
                                                    <div class="avatar-list-stacked mt-2 mt-sm-0">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/5.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                    +2
                                                    </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="card custom-card">
                                        <div class="">
                                        <div class="p-3 d-sm-flex align-items-top">
                                            <div class="me-3 wd-200">
                                                <span class="">
                                                <img src="https://laravelui.spruko.com/synto/build/assets/img/gallery/4.jpg" alt="" class="w-100 rounded-1 border">
                                                </span>
                                            </div>
                                            <div class="flex-fill ">
                                                <p class="mb-1 fw-semibold lh-1 fs-14 mt-2 mt-sm-0">Minim Lorem sunt in sunt adipisicing anim est enim duis...</p>
                                                <div class="mt-3 d-flex align-items-center justify-content-between mb-md-0 mb-2">
                                                    <div>
                                                    <div class="btn-list">
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-heart-line me-2"></i>30
                                                        </button>
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-thumb-up-line me-2"></i>25k
                                                        </button>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between">
                                                    <div class="mt-3 d-flex align-items-top">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/13.jpg" alt="img">
                                                    </span>
                                                    <div class="ms-2 fw-semibold flex-fill">
                                                        <p class="mb-0 lh-1">King Berunda</p>
                                                        <span class="fs-11 text-muted op-7">Yesterday, 10:27AM</span>
                                                    </div>
                                                    </div>
                                                    <div class="avatar-list-stacked mt-2 mt-sm-0">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/5.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                    4+
                                                    </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="card custom-card">
                                        <div class="">
                                        <div class="p-3 d-sm-flex align-items-top">
                                            <div class="me-3 wd-200">
                                                <span class="">
                                                <img src="https://laravelui.spruko.com/synto/build/assets/img/gallery/5.jpg" alt="" class="w-100 rounded-1 border">
                                                </span>
                                            </div>
                                            <div class="flex-fill ">
                                                <p class="mb-1 fw-semibold lh-1 fs-14 mt-2 mt-sm-0">Minim Lorem sunt in sunt adipisicing anim est enim duis...</p>
                                                <div class="mt-3 d-flex align-items-center justify-content-between mb-md-0 mb-2">
                                                    <div>
                                                    <div class="btn-list">
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-heart-line me-2"></i>30
                                                        </button>
                                                        <button class="btn rounded-pill btn-sm btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri ri-thumb-up-line me-2"></i>25k
                                                        </button>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between">
                                                    <div class="mt-3 d-flex align-items-top">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                    </span>
                                                    <div class="ms-2 fw-semibold flex-fill">
                                                        <p class="mb-0 lh-1">Michael Jeremy</p>
                                                        <span class="fs-11 text-muted op-7">08 Aug 2022</span>
                                                    </div>
                                                    </div>
                                                    <div class="avatar-list-stacked mt-2 mt-sm-0">
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/5.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-xs avatar-rounded">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                    4+
                                                    </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-primary btn-sm">View More</button>
                                    </div>
                                </div>
                                <div class="tab-pane fade p-0 border-0" id="gallery-tab-pane"
                                    role="tabpanel" aria-labelledby="gallery-tab-pane" tabindex="0">
                                    <div class="row">
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/2.png" alt="img">
                                                    </span>
                                                    <div class="mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Tailwind Ui Web Application</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/5.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-success-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Completed</span>
                                                    <p class="text-muted fs-14 mb-0">15-12-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/1.png" alt="img">
                                                    </span>
                                                    <div class=" mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Synto Ui Mobile Application</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-warning-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Inprogress</span>
                                                    <p class="text-muted fs-14 mb-0">15-12-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light  me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/3.png" alt="img">
                                                    </span>
                                                    <div class="mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Valex Laravel Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-warning-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Inprogress</span>
                                                    <p class="text-muted fs-14 mb-0">15-12-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/4.png" alt="img">
                                                    </span>
                                                    <div class="mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Zanex Laravel Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-primary-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>New Project</span>
                                                    <p class="text-muted fs-14 mb-0">11-2-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/5.png" alt="img">
                                                    </span>
                                                    <div class="mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Adminor Laravel Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-primary-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>New Project</span>
                                                    <p class="text-muted fs-14 mb-0">11-2-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light  me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/6.png" alt="img">
                                                    </span>
                                                    <div class=" mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Client Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-danger-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Aborted</span>
                                                    <p class="text-muted fs-14 mb-0">13-12-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/7.png" alt="img">
                                                    </span>
                                                    <div class="mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">React Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-info-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Approved</span>
                                                    <p class="text-muted fs-14 mb-0">05-12-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light  me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/8.png" alt="img">
                                                    </span>
                                                    <div class="mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Angular Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-primary-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>New Project</span>
                                                    <p class="text-muted fs-14 mb-0">11-2-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light  me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/9.png" alt="img">
                                                    </span>
                                                    <div class="mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Vue Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-success-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Completed</span>
                                                    <p class="text-muted fs-14 mb-0">15-01-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-body">
                                                <div class="d-sm-flex align-items-center flex-wrap">
                                                    <span class="avatar avatar-lg p-2 bg-light  me-3 mb-1">
                                                    <img src="https://laravelui.spruko.com/synto/build/assets/img/logos/10.png" alt="img">
                                                    </span>
                                                    <div class=" mt-sm-0 mt-1 fw-semibold flex-fill">
                                                    <p class="mb-0 lh-1 fw-semibold fs-14">Nextjs Project</p>
                                                    <div class="avatar-list-stacked mt-2">
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/15.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/4.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/3.jpg" alt="img">
                                                        </span>
                                                        <span class="avatar avatar-xs avatar-rounded">
                                                        <img src="https://laravelui.spruko.com/synto/build/assets/img/users/12.jpg" alt="img">
                                                        </span>
                                                        <a class="avatar avatar-xs bg-light avatar-rounded border-2 text-default" href="javascript:void(0);">
                                                        4+
                                                        </a>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="d-sm-flex justify-content-between mt-3">
                                                    <span class="badge bg-warning-transparent"><i class="bi bi-circle-fill fs-8 op-7 me-1"></i>Inprogress    </span>
                                                    <p class="text-muted fs-14 mb-0">15-04-2022</p>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="text-center mt-3">
                                        <button class="btn btn-primary btn-sm">View More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="col-xxl-3">
                <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Skills</div>
                </div>
                <div class="crad-body">
                    <div class="p-4 border-bottom border-block-end-dashed">
                        <div>
                            <a href="javascript:void(0);">
                            <span class="badge bg-light text-default me-1 mb-2 rounded-pill">{{$lead->trade_one}}</span>
                            </a>
                            <a href="javascript:void(0);">
                            <span class="badge bg-light text-default me-1 mb-2 rounded-pill">{{$lead->category_one}} </span>
                            </a>    
                            <a href="javascript:void(0);">
                            <span class="badge bg-light text-default me-1 mb-2 rounded-pill">{{$lead->trade_two}} </span>
                            </a>
                            <a href="javascript:void(0);">
                            <span class="badge bg-light text-default me-1 mb-2 rounded-pill">{{$lead->category_two}} </span>
                            </a>
                            <a href="javascript:void(0);">
                            <span class="badge bg-light text-default me-1 mb-2 rounded-pill">{{$lead->trade_three}}</span>
                            </a>
                            <a href="javascript:void(0);">
                            <span class="badge bg-light text-default me-1 mb-2 rounded-pill">{{$lead->category_three}} </span>
                            </a>
                          </div>
                    </div>
                </div>
                </div>
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Documents</div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-xxl-4 col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                @if($lead->front_addhar_img)
                                <a href="{{asset($lead->front_addhar_img)}}" title="View Addhar Card" class="glightbox card" target="_blank" data-gallery="gallery1">
                                <img src="{{asset($lead->front_addhar_img)}}" alt="image" >
                                </a>
                                @endif
                            </div>
                            <div class="col-xxl-4 col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                @if($lead->back_addhar_img)
                                <a href="{{asset($lead->back_addhar_img)}}" title="View Addhar Card" class="glightbox card" target="_blank" data-gallery="gallery1">
                                <img src="{{asset($lead->back_addhar_img)}}" alt="image" >
                                </a>
                                @endif
                            </div>
                            <div class="col-xxl-4 col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                @if($lead->pan_img)
                                <a href="{{asset($lead->pan_img)}}" title="View Pancard" class="glightbox card" target="_blank" data-gallery="gallery1">
                                <img src="{{asset($lead->pan_img)}}" alt="image" >
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Profile Image</div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-xxl-6 col-xl-6 col-lg-3 col-md-6 col-sm-12">
                                @if($lead->profile_img)
                                <a href="{{asset($lead->profile_img)}}" title="View Profile Image" class="glightbox card" target="_blank" data-gallery="gallery1">
                                <img src="{{asset($lead->profile_img)}}" alt="image" >
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End::row-1 -->
    </div>
    </div>
@endsection