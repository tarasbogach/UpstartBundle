# SfNixUpstartBundle
```yml
#Example configuration
upstart:
    project: imaging
    default:
        verbose: 1
        native: {setuid: www-data}
    job:
        imageResizer: {quantity: 10, command: "rabbitmq:consumer imageResizer -w"}
        faceRecognizer: {quantity: 5, native: {exec: "python faceRecognizer.py", killSignal: "SIGKILL"}
```