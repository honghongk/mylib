#!/bin/sh

# https://zetawiki.com/wiki/CVE-2014-6271_%EC%89%98%EC%87%BC%ED%81%AC_Bash_%EC%B7%A8%EC%95%BD%EC%A0%90_%EC%A1%B0%EC%B9%98


# 쉘쇼크 취약점 확인
env x='() { :;}; echo vulnerable' bash -c "echo this is a test"