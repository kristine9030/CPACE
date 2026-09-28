@extends('errors.layout')

@section('title', 'Access denied')
@section('icon', 'fa-lock')
@section('code', '403')
@section('heading', 'You don\'t have access to this page')
@section('message', $exception->getMessage() && $exception->getMessage() !== 'Forbidden'
    ? $exception->getMessage()
    : 'Your account doesn\'t have permission to view this. If you think this is a mistake, contact your Program Chair.')
