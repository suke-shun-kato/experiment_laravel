FROM quay.io/minio/minio

# args: で定義した変数をFROMの後でも使えるようにした
ARG BUCKET_NAME

# ディレクトリを作成することで、${BUCKET_NAME} の名前のバケットを作成
RUN mkdir -p /data/${BUCKET_NAME}
