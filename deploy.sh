#!/bin/bash
git add .
git commit -m "Автоматический коммит $(date +"%Y-%m-%d %H:%M:%S")"
git push
