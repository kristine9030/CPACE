@extends('errors.layout')

@section('title', 'Too many requests')
@section('icon', 'fa-gauge-high')
@section('code', '429')
@section('heading', 'Slow down a little')
@section('message', 'You\'ve made too many requests in a short time. Wait a moment and try again.')
