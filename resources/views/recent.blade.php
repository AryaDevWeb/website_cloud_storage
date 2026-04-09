@php
  $pageTitle = 'Recent Files';
  $pageDesc  = "Files you've recently uploaded or accessed";
  $section   = 'recent';
  $emptyTitle = 'No recent files';
  $emptyDesc  = 'Upload your first file to see it here.';
  $emptyIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
@endphp
@include('file_explorer')
