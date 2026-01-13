# Usamos la imagen oficial de Nginx como base
FROM nginx

# Copiamos el archivo principal de HTML al directorio de contenido estático de Nginx
COPY fraganzza_pro /usr/share/nginx/html

# Puerto en el que Nginx escuchará
EXPOSE 80

# Comando para iniciar Nginx en primer plano
CMD ["nginx", "-g", "daemon off;"]
